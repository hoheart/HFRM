package com.hoheart.hfc;

import android.content.Context;
import android.os.Handler;
import android.util.AttributeSet;
import android.view.MotionEvent;
import android.view.VelocityTracker;
import android.view.View;
import android.view.ViewConfiguration;
import android.view.ViewGroup;
import android.view.animation.Animation;
import android.view.animation.Animation.AnimationListener;
import android.view.animation.AnimationUtils;

public class SlideSwitchView extends ViewGroup {

	public interface OnSwitchListener {

		public void onSwitch(int pos);
	};

	private int mWidthMeasureSpec = 0;
	private int mHeightMeasureSpec = 0;

	private int mCurrentPos = 0;
	private View mPrev = null;
	private View mCurrent = null;
	private View mNext = null;

	private VelocityTracker mVelocityTracker = null;
	private int mMinimumVelocity = 0;
	private int mTouchSlop = 0;
	private int mLastX = 0;
	private int mLastY = 0;

	private Adapter mAdapter = null;
	private OnSwitchListener mOnSwitchListener = null;

	public SlideSwitchView(Context context, AttributeSet attrs, int defStyle) {
		super(context, attrs, defStyle);

		init();
	}

	public SlideSwitchView(Context context, AttributeSet attrs) {
		super(context, attrs);

		init();
	}

	public SlideSwitchView(Context context) {
		super(context);

		init();
	}

	public void setAdapter(Adapter a) {
		mAdapter = null;
		mAdapter = a;

		removeAllViews();

		Handler h = new Handler();
		h.post(new Runnable() {

			@Override
			public void run() {
				mCurrentPos = mAdapter.getCurrentPosition();
				mNext = mAdapter.getView(mCurrentPos, null,
						SlideSwitchView.this);
				if (null != mNext) {
					--mCurrentPos;// 因为是第一次显示，但也是模拟显示的下一个，所以减一。
				}
				mCurrent = mAdapter.getView(mCurrentPos - 1, null,
						SlideSwitchView.this);

				showAnother(true);
			}
		});
	}

	public void setOnSwitchListener(OnSwitchListener l) {
		mOnSwitchListener = l;
	}

	private void init() {
		ViewConfiguration configuration = ViewConfiguration.get(getContext());
		mTouchSlop = configuration.getScaledTouchSlop();
		mMinimumVelocity = configuration.getScaledMinimumFlingVelocity();
	}

	@Override
	protected void onMeasure(int widthMeasureSpec, int heightMeasureSpec) {
		super.onMeasure(widthMeasureSpec, heightMeasureSpec);

		mWidthMeasureSpec = widthMeasureSpec;
		mHeightMeasureSpec = heightMeasureSpec;
	}

	@Override
	protected void onLayout(boolean changed, int l, int t, int r, int b) {
		int pdl = getPaddingLeft();
		int pdt = getPaddingTop();

		if (null != mCurrent) {
			if (mCurrent.isLayoutRequested()) {
				mCurrent.measure(mWidthMeasureSpec, mHeightMeasureSpec);

				int cr = pdl + mCurrent.getMeasuredWidth();
				int cb = pdt + mCurrent.getMeasuredHeight();
				mCurrent.layout(pdl, pdt, cr, cb);
			}
		}
	}

	@Override
	public boolean onInterceptTouchEvent(MotionEvent ev) {
		if (MotionEvent.ACTION_DOWN == ev.getAction()) {
			mLastX = (int) ev.getX();
			mLastY = (int) ev.getY();
		}

		if (MotionEvent.ACTION_MOVE == ev.getAction()) {
			int x = (int) ev.getX();
			int y = (int) ev.getY();
			int diffX = x - mLastX;
			int diffY = y - mLastY;
			if (Math.abs(diffX) > Math.abs(diffY)
					&& Math.abs(Math.abs(diffX) - Math.abs(diffY)) > mTouchSlop) {
				if (mVelocityTracker == null) {
					mVelocityTracker = VelocityTracker.obtain();
				}
				mVelocityTracker.addMovement(ev);

				return true;
			}
		}

		return false;
	}

	@Override
	public boolean onTouchEvent(MotionEvent event) {
		switch (event.getAction()) {
		case MotionEvent.ACTION_DOWN:
			return true;

		case MotionEvent.ACTION_MOVE:
			if (mVelocityTracker == null) {
				mVelocityTracker = VelocityTracker.obtain();
			}
			mVelocityTracker.addMovement(event);

			int x = (int) event.getX();
			int diffX = x - mLastX;
			if (diffX > 0) {
				if (mAdapter.isFirst(mCurrentPos)) {
					return false;
				}
			} else {
				if (mAdapter.isLast(mCurrentPos)) {
					return false;
				}
			}

			break;

		case MotionEvent.ACTION_CANCEL:
		case MotionEvent.ACTION_UP:
			final VelocityTracker vt = mVelocityTracker;
			if (null == vt) {
				return false;
			}

			vt.computeCurrentVelocity(1000);
			int vx = (int) vt.getXVelocity();
			int vy = (int) vt.getYVelocity();
			if (Math.abs(vx) > mMinimumVelocity
					&& Math.abs(vx) - Math.abs(vy) > mMinimumVelocity) {
				if (vx > 0) {
					if (mAdapter.isBeforeFirst(mCurrentPos - 1)) {
						return false;
					}
				} else {
					if (mAdapter.isAfterLast(mCurrentPos + 1)) {
						return false;
					}
				}

				onFling(-vx);

				return true;
			}
		}

		return false;
	}

	public void onFling(int x) {
		if (x < 0) {
			showAnother(false);
		} else {
			showAnother(true);
		}
	}

	private void showAnother(final boolean isNext) {
		AnimationListener l = new AnimationListener() {

			@Override
			public void onAnimationEnd(Animation animation) {
				Handler h = new Handler();
				h.post(new Runnable() {

					@Override
					public void run() {
						if (isNext) {
							mNext = mAdapter.getView(mCurrentPos + 1, mNext,
									SlideSwitchView.this);
						} else {
							mPrev = mAdapter.getView(mCurrentPos - 1, mPrev,
									SlideSwitchView.this);
						}
					}
				});
			}

			@Override
			public void onAnimationRepeat(Animation animation) {
			}

			@Override
			public void onAnimationStart(Animation animation) {
			}

		};

		if (isNext) {
			if (null == mNext) {
				return;
			}

			View tmp = mPrev;
			mPrev = mCurrent;

			if (null != mCurrent) {
				Animation a = AnimationUtils.loadAnimation(getContext(),
						com.hoheart.hfc.R.anim.slide_out_left);
				a.setAnimationListener(l);
				mCurrent.startAnimation(a);

				removeAllViews();
			}

			mCurrent = mNext;
			addView(mCurrent);

			Animation a = AnimationUtils.loadAnimation(getContext(),
					com.hoheart.hfc.R.anim.slide_in_right);
			a.setAnimationListener(l);
			mCurrent.startAnimation(a);

			mNext = tmp;
		} else {
			if (null == mPrev) {
				return;
			}

			View tmp = mNext;
			mNext = mCurrent;

			if (null != mCurrent) {
				Animation a = AnimationUtils.loadAnimation(getContext(),
						com.hoheart.hfc.R.anim.slide_out_right);
				a.setAnimationListener(l);
				mCurrent.setAnimation(a);

				removeAllViews();
			}

			mCurrent = mPrev;
			addView(mCurrent);
			Animation a = AnimationUtils.loadAnimation(getContext(),
					com.hoheart.hfc.R.anim.slide_in_left);
			a.setAnimationListener(l);
			mCurrent.startAnimation(a);

			mPrev = tmp;
		}

		if (isNext) {
			++mCurrentPos;
		} else {
			--mCurrentPos;
		}

		if (null != mOnSwitchListener) {
			mOnSwitchListener.onSwitch(mCurrentPos);
		}
	}
}
