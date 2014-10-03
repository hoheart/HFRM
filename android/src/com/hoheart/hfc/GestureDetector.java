package com.hoheart.hfc;

import android.content.Context;
import android.util.Log;
import android.view.MotionEvent;
import android.view.VelocityTracker;
import android.view.ViewConfiguration;

public class GestureDetector {

	public static interface OnDownListener {

		public boolean onDown(MotionEvent ev);
	}

	public static interface OnMoveListener {

		public boolean onMove(MotionEvent ev, int distanceX, int distanceY);
	}

	public static interface OnFlingListener {

		public void onFling(MotionEvent ev, int x, int y);
	}

	private int mTouchSlop = 0;
	private int mMinimumVelocity = 0;
	private VelocityTracker mVelocityTracker = null;
	private int mLastX = 0;
	private int mLastY = 0;

	private OnDownListener mOnDownListener = null;
	private OnMoveListener mOnMoveListener = null;
	private OnFlingListener mOnFlingListener = null;

	public GestureDetector(Context context) {
		final ViewConfiguration configuration = ViewConfiguration.get(context);
		mTouchSlop = configuration.getScaledTouchSlop();
		mMinimumVelocity = configuration.getScaledMinimumFlingVelocity();
	}

	public void setOnDownListener(OnDownListener l) {
		mOnDownListener = l;
	}

	public void setOnMoveListener(OnMoveListener l) {
		mOnMoveListener = l;
	}

	public void setOnFlingListener(OnFlingListener l) {
		mOnFlingListener = l;
	}

	public boolean onTouchEvent(MotionEvent ev) {
		if (mVelocityTracker == null) {
			mVelocityTracker = VelocityTracker.obtain();
		}

		mVelocityTracker.addMovement(ev);

		final int x = (int) ev.getX();
		final int y = (int) ev.getY();
		final int action = ev.getAction();
		switch (action) {
		case MotionEvent.ACTION_DOWN:
			Log.d("tour", "gesture detector down");
			if (null != mOnDownListener) {
				mOnDownListener.onDown(ev);
			}

			mLastX = (int) ev.getX();
			mLastY = (int) ev.getY();

			break;
		case MotionEvent.ACTION_MOVE:
			Log.d("tour", "gesture detector move");
			if (null != mOnMoveListener) {
				int diffX = x - mLastX;
				int diffY = y - mLastY;
				mLastX = x;
				mLastY = y;
				if (Math.abs(diffX) < mTouchSlop) {
					diffX = 0;
				}
				if (Math.abs(diffY) < mTouchSlop) {
					diffY = 0;
				}

				if (0 != diffX || 0 != diffY) {
					mOnMoveListener.onMove(ev, diffX, diffY);
				}
			}

			break;
		case MotionEvent.ACTION_CANCEL:
		case MotionEvent.ACTION_UP:
			Log.d("tour", "gesture detector up:" + ev.getAction());
			if (null != mOnFlingListener) {
				final VelocityTracker vt = mVelocityTracker;
				vt.computeCurrentVelocity(1000);
				int vx = (int) vt.getXVelocity();
				int vy = (int) vt.getYVelocity();
				Log.d("tour", "gesture vx " + vx);
				if (Math.abs(vx) < mMinimumVelocity) {
					vx = 0;
				}
				if (Math.abs(vy) < mMinimumVelocity) {
					vy = 0;
				}

				if (0 != vx || 0 != vy) {
					mOnFlingListener.onFling(ev, -vx, -vy);
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
}
