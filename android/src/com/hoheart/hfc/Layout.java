package com.hoheart.hfc;

import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;

import android.content.Context;
import android.content.res.TypedArray;
import android.graphics.Rect;
import android.util.AttributeSet;
import android.view.MotionEvent;
import android.view.VelocityTracker;
import android.view.View;
import android.view.ViewConfiguration;
import android.view.ViewGroup;
import android.view.ViewParent;
import android.widget.SlidingDrawer;

/**
 * 自定义布局，有换行的功能。
 * 
 * @author Hoheart
 * 
 */
public class Layout extends ViewGroup {

	public static class LayoutParams extends MarginLayoutParams {

		public LayoutParams(Context c, AttributeSet attrs) {
			super(c, attrs);
		}

		public LayoutParams(int width, int height) {
			super(width, height);
		}

		public LayoutParams(android.view.ViewGroup.LayoutParams lp) {
			super(lp);
		}

		public LayoutParams(MarginLayoutParams lp) {
			super(lp);
		}
	}

	/**
	 * 方向
	 */
	public static final int NONE = 0;
	public static final int HORIZONTAL = 1;
	public static final int VERTICAL = 2;
	public static final int MULTIROW = 4;
	public static final int BOTH = HORIZONTAL | VERTICAL;

	private int mOrientation = HORIZONTAL;
	private int mFillViewport = NONE;

	private Adapter mAdapter = null;

	private int mWidthMeasureSpec = 0;
	private int mHeightMeasureSpec = 0;

	private int mChildrenStartPos = 0;
	private RecycleBin mRecycleBin = new RecycleBin();

	/**
	 * 滚动相关
	 */
	private int mScroll = NONE;// 在什么方位上出现滚动条
	private OverScroller mScroller = null;
	private VelocityTracker mVelocityTracker = null;
	private boolean mIsBeingDragged = false;
	private int mMinimumVelocity = 0;
	private int mMaximumFlingVelocity = 4000;
	private int mTouchSlop = 0;
	private int mLastMotionX = 0;
	private int mLastMotionY = 0;

	public Layout(Context context) {
		super(context);

		init(context);
	}

	public Layout(Context context, AttributeSet attrs, int defStyle) {
		super(context, attrs, defStyle);

		init(context);
	}

	public Layout(Context context, AttributeSet attrs) {
		super(context, attrs);

		init(context);

		TypedArray a = context
				.obtainStyledAttributes(attrs, R.styleable.Layout);

		int orientation = a.getInt(R.styleable.Layout_orientation, HORIZONTAL);
		setOrientation(orientation);

		int scroll = a.getInt(R.styleable.Layout_scroll, NONE);
		setScroll(scroll);

		int fillViewport = a.getInt(R.styleable.Layout_fillViewport, NONE);
		setFillViewport(fillViewport);
	}

	private void init(Context context) {
		mScroller = new OverScroller(context);

		final ViewConfiguration configuration = ViewConfiguration.get(context);
		mTouchSlop = configuration.getScaledTouchSlop();
		mMinimumVelocity = configuration.getScaledMinimumFlingVelocity();
	}

	public void setOrientation(int orientation) {
		mOrientation = orientation;
	}

	public void setScroll(int scroll) {
		mScroll = scroll;
	}

	public void setFillViewport(int fill) {
		mFillViewport = fill;
	}

	public void setAdapter(Adapter a) {
		removeAllViews();

		// 如果前面有滚动了，虽然removeAllViews()了，但scroll相关的东西没有改变。
		// 当一个Child都没有的时候，scrollTo会报错。
		try {
			scrollTo(0, 0);
		} catch (Exception e) {
		}

		mChildrenStartPos = 0;

		mRecycleBin.clear();

		mAdapter = a;
	}

	public Adapter getAdapter() {
		return mAdapter;
	}

	@Override
	protected ViewGroup.LayoutParams generateDefaultLayoutParams() {
		return new LayoutParams(LayoutParams.WRAP_CONTENT,
				LayoutParams.WRAP_CONTENT);
	}

	@Override
	protected ViewGroup.LayoutParams generateLayoutParams(
			ViewGroup.LayoutParams p) {
		return new LayoutParams(p);
	}

	@Override
	public ViewGroup.LayoutParams generateLayoutParams(AttributeSet attrs) {
		return new LayoutParams(getContext(), attrs);
	}

	/*
	 * 要算出本view显示的高宽，同时算出其内容的高宽，因为可能外部程序会调用其滚动功能 。
	 * 
	 * @see android.view.View#onMeasure(int, int)
	 */
	@Override
	protected void onMeasure(int widthMeasureSpec, int heightMeasureSpec) {
		// 计算新的measureSpec的值并保存到成员变量
		mWidthMeasureSpec = widthMeasureSpec;
		mHeightMeasureSpec = heightMeasureSpec;
		if (HORIZONTAL == (mScroll & HORIZONTAL)) {
			mWidthMeasureSpec = MeasureSpec.makeMeasureSpec(
					MeasureSpec.UNSPECIFIED, 0);
		}
		if (VERTICAL == (mScroll & VERTICAL)) {
			mHeightMeasureSpec = MeasureSpec.makeMeasureSpec(
					MeasureSpec.UNSPECIFIED, 0);
		}

		final int wmode = MeasureSpec.getMode(widthMeasureSpec);
		final int hmode = MeasureSpec.getMode(heightMeasureSpec);
		int maxWidth = MeasureSpec.getSize(widthMeasureSpec);
		int maxHeight = MeasureSpec.getSize(heightMeasureSpec);
		// 如果都是精确指定的值，就是它了
		if (MeasureSpec.EXACTLY == wmode && MeasureSpec.EXACTLY == hmode) {
			setMeasuredDimension(maxWidth, maxHeight);

			return;
		}

		if (MeasureSpec.UNSPECIFIED == wmode) {
			maxWidth = Integer.MAX_VALUE;
		}
		if (MeasureSpec.UNSPECIFIED == hmode) {
			maxHeight = Integer.MAX_VALUE;
		}

		// 到这里，表示hmode和wmode其中有一个是需要根据内容来决定本Layout的高宽的。
		int pdl = getPaddingLeft();
		int pdr = getPaddingRight();
		int pdt = getPaddingTop();
		int pdb = getPaddingBottom();

		Rect cr = measureContent(maxWidth - pdl - pdr, maxHeight - pdt - pdb,
				true);

		// 3.根据指定的mode和fillViewport值，重新计算本view的显示高宽
		int mw = maxWidth, mh = maxHeight;
		if (MeasureSpec.AT_MOST == wmode || MeasureSpec.UNSPECIFIED == wmode) {
			mw = cr.right + pdl + pdr;

			if (HORIZONTAL == (mFillViewport & HORIZONTAL)) {
				if (maxWidth > mw) {
					mw = maxWidth;
				}
			}
		}
		if (MeasureSpec.AT_MOST == hmode || MeasureSpec.UNSPECIFIED == hmode) {
			mh = cr.bottom + pdt + pdb;

			if (VERTICAL == (mFillViewport & VERTICAL)) {
				if (maxHeight > mh) {
					mh = maxHeight;
				}
			}
		}

		setMeasuredDimension(mw, mh);
	}

	@Override
	protected void onLayout(boolean changed, int l, int t, int r, int b) {
		for (int i = 0; i < getChildCount(); ++i) {
			View c = getChildAt(i);
			if (c.isLayoutRequested()) {
				Rect cr = getChildLayout(c, true);
				layoutChild(c, i, cr);

				if (isChildCovered(c, cr)) {
					return;
				}
			}
		}

		for (int i = mChildrenStartPos + getChildCount(); null != mAdapter
				&& !mAdapter.isAfterLast(i); ++i) {
			int[] type = new int[1];
			View c = obtainChild(i, type);
			if (View.GONE == c.getVisibility()) {
				continue;
			}

			Rect cr = getChildLayout(c, true);
			addAndLayoutChild(c, i, cr);

			if (isChildCovered(c, cr)) {
				break;
			}
		}
	}

	private void addAndLayoutChild(View c, int i, Rect cr) {
		LayoutParams lp = (LayoutParams) c.getLayoutParams();
		addViewInLayout(c, i - mChildrenStartPos, lp);
		c.layout(cr.left + lp.leftMargin, cr.top + lp.topMargin, cr.right
				- lp.rightMargin, cr.bottom - lp.bottomMargin);
	}

	private void layoutChild(View c, int i, Rect cr) {
		LayoutParams lp = (LayoutParams) c.getLayoutParams();
		c.layout(cr.left + lp.leftMargin, cr.top + lp.topMargin, cr.right
				- lp.rightMargin, cr.bottom - lp.bottomMargin);
	}

	/**
	 * 根据内容决定本Layout的尺寸时，本函数完成内容的测量。在测量过程中，一旦超过允许的最大值，就不再继续测量。
	 * 
	 * @param maxWidth
	 * @param maxHeight
	 * @param contentSize
	 * @return
	 */
	private Rect measureContent(int maxWidth, int maxHeight, boolean fillDown) {
		Rect r = new Rect();

		int index;
		for (index = 0; index < getChildCount(); ++index) {
			View c = getChildAt(index);
			r = getChildLayout(c, r, maxWidth, fillDown);

			if (isChildCovered(c, r, maxWidth, maxHeight)) {
				if (r.right > maxWidth) {
					r.right = maxWidth;
				}
				if (r.bottom > maxHeight) {
					r.bottom = maxHeight;
				}

				return r;
			}
		}

		if (null != mAdapter) {
			Rect lr = new Rect();
			for (int i = index; !mAdapter.isBeforeFirst(i)
					&& !mAdapter.isAfterLast(i); i = (fillDown ? i + 1 : i - 1)) {
				int[] type = new int[1];
				View c = obtainChild(i, type);
				lr = getChildLayout(c, lr, maxWidth, fillDown);
				if (r.left > lr.left) {
					r.left = lr.left;
				}
				if (r.top > lr.top) {
					r.top = lr.top;
				}
				if (r.right < lr.right) {
					r.right = lr.right;
				}
				if (r.bottom < lr.bottom) {
					r.bottom = lr.bottom;
				}

				mRecycleBin.put(type[0], c);

				if (isChildCovered(c, lr, maxWidth, maxHeight)) {
					if (r.right > maxWidth) {
						r.right = maxWidth;
					}
					if (r.bottom > maxHeight) {
						r.bottom = maxHeight;
					}

					break;
				}
			}
		}

		return r;
	}

	/**
	 * 判断该child是否已经被覆盖了（即没有全部显示出来）。
	 * 
	 * @param child
	 * @param rect
	 *            该child的rect，包括margins
	 * @param maxX
	 * @param maxY
	 * @return
	 */
	private boolean isChildCovered(View child, Rect rect, int maxX, int maxY) {
		if (null == rect) {
			if (child.isLayoutRequested()) {
				return true;
			}
		}

		LayoutParams lp = (LayoutParams) child.getLayoutParams();
		switch (mOrientation) {
		case HORIZONTAL:
			if (rect.right + lp.rightMargin > maxX) {
				return true;
			}

			break;
		case VERTICAL:
		case MULTIROW:
			if (rect.bottom + lp.bottomMargin > maxY) {
				return true;
			}

			break;
		}

		return false;
	}

	private boolean isChildCovered(View child, Rect rect) {
		int maxX = getScrollX() + getMeasuredWidth() - getPaddingLeft()
				- getPaddingRight();
		int maxY = getScrollY() + getMeasuredHeight() - getPaddingBottom();
		return isChildCovered(child, rect, maxX, maxY);
	}

	private View obtainChild(int position, int[] type) {
		type[0] = mAdapter.getViewType(position);
		View c = mRecycleBin.get(type[0]);
		c = mAdapter.getView(position, c, this);
		if (null == c.getLayoutParams()) {
			c.setLayoutParams(generateDefaultLayoutParams());
		}

		return c;
	}

	/**
	 * 返回的rect包括margins
	 * 
	 * @param child
	 * @param isDown
	 * @return
	 */
	private Rect getChildLayout(View child, boolean fillDown) {
		Rect relativeRect = new Rect(getPaddingLeft(), getPaddingTop(), 0, 0);

		int rci = indexOfChild(child);
		View rc = null;
		if (fillDown) {
			if (rci >= 0) {// 如果已经是child了，就取前面的作为参考；否则取最后一个作为参考。
				rc = getChildAt(rci - 1);
			} else {
				rc = getChildAt(getChildCount() - 1);
				if (null != rc && rc.isLayoutRequested()) {
					rc = null;
				}
			}
		} else {
			if (rci >= 0) {
				rc = getChildAt(rci + 1);
			} else {
				rc = getChildAt(0);
				if (null != rc && rc.isLayoutRequested()) {
					rc = null;
				}
			}
		}
		if (null != rc && !rc.isLayoutRequested()) {
			LayoutParams lp = (LayoutParams) rc.getLayoutParams();
			relativeRect.left = rc.getLeft() - lp.leftMargin;
			relativeRect.top = rc.getTop() - lp.topMargin;
			relativeRect.right = rc.getRight() + lp.rightMargin;
			relativeRect.bottom = rc.getBottom() + lp.bottomMargin;
		}

		return getChildLayout(child, relativeRect, -1, fillDown);
	}

	/**
	 * 返回的rect包括margins
	 * 
	 * @param child
	 * @param isDown
	 * @return
	 */
	private Rect getChildLayout(View child, Rect relativeRect, int maxWidth,
			boolean fillDown) {
		LayoutParams lp = (LayoutParams) child.getLayoutParams();
		if (!child.isLayoutRequested()) {
			int l = child.getLeft() - lp.leftMargin;
			int t = child.getTop() - lp.topMargin;
			int r = child.getRight() + lp.rightMargin;
			int b = child.getBottom() + lp.bottomMargin;
			return new Rect(l, t, r, b);
		}

		measureChild(child, mWidthMeasureSpec, mHeightMeasureSpec);
		final int childWidth = child.getMeasuredWidth();
		final int childHeight = child.getMeasuredHeight();

		Rect r = new Rect();
		switch (mOrientation) {
		case HORIZONTAL: {
			if (fillDown) {
				r.left = relativeRect.right;
			} else {
				r.left = relativeRect.left - lp.rightMargin - childWidth
						- lp.leftMargin;
			}
			r.top = relativeRect.top;

			break;
		}
		case VERTICAL: {
			if (fillDown) {
				r.top = relativeRect.bottom;
			} else {
				r.top = relativeRect.top - lp.bottomMargin - childHeight
						- lp.topMargin;
			}
			r.left = relativeRect.left;

			break;
		}
		case MULTIROW: {
			if (-1 == maxWidth) {
				maxWidth = getMeasuredWidth() - getPaddingLeft()
						- getPaddingRight();
				if (HORIZONTAL == (mScroll & HORIZONTAL)) {
					maxWidth = Integer.MAX_VALUE;
				}
			}

			if (fillDown) {
				r.left = relativeRect.right;
				r.top = relativeRect.top;
				if (relativeRect.right + lp.leftMargin + childWidth
						+ lp.rightMargin > maxWidth) {
					r.left = getPaddingLeft();
					// TODO 这个有问题，一行中可能有比relative高的元素。
					r.top = relativeRect.bottom;
				}
			} else {
				// TODO 暂时没有写这个，这个比较复杂，要把一行取出来layout之后，才能决定当前child的layout
			}

			break;
		}
		}

		r.right = r.left + lp.leftMargin + childWidth + lp.rightMargin;
		r.bottom = r.top + lp.topMargin + childHeight + lp.bottomMargin;

		return r;
	}

	@Override
	public void computeScroll() {
		if (mScroller.computeScrollOffset()) {
			int x = mScroller.getCurrX();
			int y = mScroller.getCurrY();

			scrollTo(x, y);

			// Keep on drawing until the animation has finished.
			postInvalidate();
		}
	}

	@Override
	public void scrollTo(int x, int y) {
		boolean fillDown = isFillDown(x - getScrollX(), y - getScrollY());

		super.scrollTo(x, y);

		int pos = 0;
		Rect vr = getVisilbeRect();
		if (fillDown) {
			// 判断内容够不够
			while (null != mAdapter
					&& !mAdapter.isAfterLast(pos = mChildrenStartPos
							+ getChildCount())
					&& !isContentFullFill(vr, fillDown)) {
				int[] type = new int[1];
				View c = obtainChild(pos, type);
				Rect r = getChildLayout(c, fillDown);
				addAndLayoutChild(c, pos, r);

				// 为了节约cpu资源，有填充时，才clear
				clearAllCovered(vr, fillDown);
			}

			if (null == mAdapter
					|| mAdapter
							.isAfterLast(mChildrenStartPos + getChildCount())) {
				View c = getChildAt(getChildCount() - 1);
				if (!isChildCovered(c, getChildLayout(c, fillDown))) {
					LayoutParams lp = (LayoutParams) c.getLayoutParams();
					int maxX = c.getRight() + lp.rightMargin
							- getPaddingRight() - getMeasuredWidth()
							- getPaddingLeft();
					if (maxX < 0) {
						maxX = 0;
					}
					int maxY = c.getBottom() + lp.bottomMargin
							- getPaddingTop() - getMeasuredHeight()
							- getPaddingBottom();
					if (maxY < 0) {
						maxY = 0;
					}
					mScroller.notifyHorizontalEdgeReached(mScroller.getCurrX(),
							maxX, mTouchSlop);
					mScroller.notifyVerticalEdgeReached(mScroller.getCurrY(),
							maxY, mTouchSlop);
				}
			}
		} else {
			// 判断内容够不够
			while (!isContentFullFill(vr, fillDown) && mChildrenStartPos > 0) {
				--mChildrenStartPos;

				int[] type = new int[1];
				View c = obtainChild(mChildrenStartPos, type);
				Rect r = getChildLayout(c, fillDown);
				addAndLayoutChild(c, mChildrenStartPos, r);

				clearAllCovered(vr, fillDown);
			}
		}
	}

	private boolean isContentFullFill(Rect vr, boolean fillDown) {
		switch (mOrientation) {
		case HORIZONTAL:
			if (fillDown) {
				View c = getChildAt(getChildCount() - 1);
				LayoutParams lp = (LayoutParams) c.getLayoutParams();
				if (c.getRight() + lp.rightMargin > vr.right) {
					return true;
				}
			} else {
				View c = getChildAt(0);
				LayoutParams lp = (LayoutParams) c.getLayoutParams();
				if (c.getLeft() - lp.leftMargin < vr.left) {
					return true;
				}
			}

			break;
		case VERTICAL:
		case MULTIROW:
			if (fillDown) {
				View c = getChildAt(getChildCount() - 1);
				LayoutParams lp = (LayoutParams) c.getLayoutParams();
				if (c.getBottom() + lp.bottomMargin > vr.bottom) {
					return true;
				}
			} else {
				View c = getChildAt(0);
				LayoutParams lp = (LayoutParams) c.getLayoutParams();
				if (c.getTop() - lp.topMargin < vr.top) {
					return true;
				}
			}

			break;
		}

		return false;
	}

	private Rect getVisilbeRect() {
		int l = getScrollX() + getPaddingLeft();
		int t = getScrollY() + getPaddingTop();
		int r = l + getMeasuredWidth() - getPaddingRight();
		int b = t + getMeasuredHeight() - getPaddingBottom();

		return new Rect(l, t, r, b);
	}

	private void clearAllCovered(Rect visibleRect, boolean fromUp) {
		while (true) {
			int i = 0;
			if (!fromUp) {
				i = getChildCount() - 1;
			}

			View c = getChildAt(i);
			LayoutParams lp = (LayoutParams) c.getLayoutParams();
			Rect childRect = new Rect(c.getLeft() - lp.leftMargin, c.getTop()
					- lp.topMargin, c.getRight() + lp.rightMargin,
					c.getBottom() + lp.bottomMargin);
			if (!Rect.intersects(visibleRect, childRect)) {
				int pos = mChildrenStartPos + indexOfChild(c);
				int type = mAdapter.getViewType(pos);

				if (fromUp) {
					++mChildrenStartPos;
				}

				removeViewInLayout(c);

				mRecycleBin.put(type, c);
			} else {
				// 这个已经在visibleRect里了，后面肯定都显示了
				break;
			}

			if (fromUp) {
				++i;
			} else {
				--i;
			}

			if (i >= getChildCount() || i < 0) {
				break;
			}
		}
	}

	@Override
	public boolean onInterceptTouchEvent(MotionEvent ev) {
		if (HORIZONTAL != (HORIZONTAL & mScroll)
				&& VERTICAL != (VERTICAL & mScroll)) {
			return false;
		}

		final int x = (int) ev.getX();
		final int y = (int) ev.getY();
		final int action = ev.getAction();
		switch (action) {
		case MotionEvent.ACTION_DOWN: {
			mLastMotionX = x;
			mLastMotionY = y;

			if (!inChild(x, y)) {
				mIsBeingDragged = false;
				break;
			}

			mIsBeingDragged = !mScroller.isFinished();

			break;
		}

		case MotionEvent.ACTION_MOVE: {
			if (HORIZONTAL == (HORIZONTAL & mScroll)) {
				final int xDiff = (int) Math.abs(x - mLastMotionX);
				if (xDiff > mTouchSlop) {// 判断child是否需要拦截
					mIsBeingDragged = true;
				}
			}

			if (VERTICAL == (VERTICAL & mScroll)) {
				final int yDiff = (int) Math.abs(y - mLastMotionY);
				if (yDiff > mTouchSlop) {// 判断child是否需要拦截
					mIsBeingDragged = true;
				}
			}

			if (mIsBeingDragged) {
				mLastMotionX = x;
				mLastMotionY = y;

				ViewParent parent = getParent();
				if (parent != null) {
					parent.requestDisallowInterceptTouchEvent(true);
				}
			}

			break;
		}

		case MotionEvent.ACTION_CANCEL:
		case MotionEvent.ACTION_UP:
			mIsBeingDragged = false;
			break;
		}

		return mIsBeingDragged;
	}

	@Override
	public boolean onTouchEvent(MotionEvent ev) {
		if (ev.getAction() == MotionEvent.ACTION_DOWN && ev.getEdgeFlags() != 0) {
			// Don't handle edge touches immediately.
			// they may actually belong to one of our descendants.
			return false;
		}

		if (mVelocityTracker == null) {
			mVelocityTracker = VelocityTracker.obtain();
		}

		mVelocityTracker.addMovement(ev);

		final int x = (int) ev.getX();
		final int y = (int) ev.getY();
		final int action = ev.getAction();
		switch (action) {
		case MotionEvent.ACTION_DOWN:
			if (!(mIsBeingDragged = inChild(x, y))) {
				return false;
			}

			/**
			 * If being flinged and user touches, stop the fling. isFinished
			 * will be false if being flinged.
			 */
			if (!mScroller.isFinished()) {
				mScroller.abortAnimation();
			}

			return true;// 让后续事件判断要不要处理，要不然，后面截获不到事件了

		case MotionEvent.ACTION_MOVE:
			if (mIsBeingDragged) {
				int xDiff = 0;
				if (HORIZONTAL == (mScroll & HORIZONTAL)) {
					xDiff = mLastMotionX - x;
					if (getScrollX() + xDiff < 0) {
						xDiff = 0 - getScrollX();
					}
				}

				int yDiff = 0;
				if (VERTICAL == (mScroll & VERTICAL)) {
					yDiff = mLastMotionY - y;
					if (getScrollY() + yDiff < 0) {
						yDiff = 0 - getScrollY();
					}
				}

				mLastMotionX = x;
				mLastMotionY = y;

				if (0 != xDiff || 0 != yDiff) {
					scrollBy(xDiff, yDiff);

					return true;
				}
			}

			return false;

		case MotionEvent.ACTION_CANCEL:
		case MotionEvent.ACTION_UP:
			if (mIsBeingDragged) {
				mIsBeingDragged = false;

				final VelocityTracker vt = mVelocityTracker;
				vt.computeCurrentVelocity(1000);
				int vx = (int) vt.getXVelocity();
				int vy = (int) vt.getYVelocity();
				if (Math.abs(vx) < mMinimumVelocity
						|| HORIZONTAL != (mScroll & HORIZONTAL)) {
					vx = 0;
				}
				if (vx < -mMaximumFlingVelocity) {
					vx = -mMaximumFlingVelocity;
				} else if (vx > mMaximumFlingVelocity) {
					vx = mMaximumFlingVelocity;
				}
				if (Math.abs(vy) < mMinimumVelocity
						|| VERTICAL != (mScroll & VERTICAL)) {
					vy = 0;
				}
				if (vy < -mMaximumFlingVelocity) {
					vy = -mMaximumFlingVelocity;
				} else if (vy > mMaximumFlingVelocity) {
					vy = mMaximumFlingVelocity;
				}

				if (vx != 0 || vy != 0) {// fling在滚动超过的时候会自己弹回来
					fling(-vx, -vy);
					invalidate();
				}

				if (mVelocityTracker != null) {
					mVelocityTracker.recycle();
					mVelocityTracker = null;
				}
			}

			break;
		}

		return true;
	}

	private void fling(int x, int y) {
		mScroller.fling(getScrollX(), getScrollY(), x, y, 0, Integer.MAX_VALUE,
				0, Integer.MAX_VALUE, mTouchSlop, mTouchSlop);
	}

	private boolean inChild(int x, int y) {
		View c = null;
		int scrollX = getScrollX();
		int scrollY = getScrollY();

		switch (mOrientation) {
		case HORIZONTAL:
			for (int i = 0; i < getChildCount(); ++i) {
				c = getChildAt(i);
				if (c.getLeft() - scrollX > x) {
					if (i > 0) {
						c = getChildAt(i - 1);

						break;
					} else {
						return false;
					}
				}
			}

			break;

		case VERTICAL:
		case MULTIROW:
			int i;
			for (i = 0; i < getChildCount(); ++i) {
				c = getChildAt(i);
				if (c.getTop() - scrollY > y) {
					if (i > 0) {
						c = getChildAt(i - 1);

						break;
					} else {
						return false;
					}
				}
			}

			if (MULTIROW != (MULTIROW & mOrientation)) {
				for (int ii = i - 1; ii >= 0; --ii) {
					if (c.getRight() - scrollX < x) {
						if (ii < i - 1) {
							c = getChildAt(ii - 1);

							break;
						} else {
							return false;
						}
					}
				}
			}

			break;
		}

		if (null != c) {
			if (SlidingDrawer.class.getName().equals(c.getClass().getName())) {
				SlidingDrawer sd = (SlidingDrawer) c;
				View h = sd.getHandle();
				View content = sd.getContent();
				Rect cr = new Rect(h.getLeft(), h.getTop(), content.getRight(),
						content.getBottom());

				return cr.contains(x, y);
			} else if (ViewGroup.class.isAssignableFrom(c.getClass())) {
				if (null == c.getBackground()) {
					ViewGroup child = (ViewGroup) c;
					View fc = child.getChildAt(0);
					if (null == fc) {
						return false;
					}
					View lc = child.getChildAt(child.getChildCount() - 1);
					Rect cr = new Rect(fc.getLeft(), fc.getTop(),
							lc.getRight(), lc.getBottom());

					return cr.contains(x, y);
				}
			}

			Rect cr = new Rect(c.getLeft() - scrollX, c.getTop() - scrollY,
					c.getRight() - scrollX, c.getBottom() - scrollY);
			return cr.contains(x, y);
		} else {
			return false;
		}
	}

	private boolean isFillDown(int diffX, int diffY) {
		switch (mOrientation) {
		case HORIZONTAL:
			return diffX > 0;

		case VERTICAL:
		case MULTIROW:
			return diffY > 0;
		}

		return true;
	}

	private class RecycleBin {

		Map<Integer, List<View>> mMap = new HashMap<Integer, List<View>>();

		public void put(int i, View c) {
			List<View> l = mMap.get(i);
			if (null == l) {
				l = new LinkedList<View>();
				mMap.put(i, l);
			}

			l.add(c);
		}

		public void clear() {
			mMap.clear();
		}

		public View get(int i) {
			List<View> l = mMap.get(i);
			if (null != l && l.size() > 0) {
				View c = l.get(0);
				l.remove(0);

				return c;
			}

			return null;
		}
	}
}