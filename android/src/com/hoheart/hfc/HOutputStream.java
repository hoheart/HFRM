package com.hoheart.hfc;

import java.io.IOException;
import java.io.OutputStream;

public class HOutputStream extends OutputStream {

	protected OutputStream mOutputStream = null;

	protected int mRate = 0;

	protected int mWroteLen = 0;
	protected int mLastNoticeLen = 0;

	protected LengthChangeListener mLengthChangeListener = null;

	public HOutputStream(OutputStream o, LengthChangeListener l) {
		mOutputStream = o;
		mLengthChangeListener = l;
	}

	public void resetWroteCounter() {
		mWroteLen = 0;
	}

	/**
	 * ���ü���Ƶ�ʣ�ÿ����rate���ֽڣ����ü�������һ�Σ�
	 * 
	 * @param rate
	 */
	public void setRate(int rate) {
		mRate = rate;
	}

	@Override
	public void write(int oneByte) throws IOException {
		mOutputStream.write(oneByte);

		++mWroteLen;

		if (mWroteLen - mLastNoticeLen >= mRate) {
			mLengthChangeListener.onLengthChanged(mLastNoticeLen, mWroteLen);

			mLastNoticeLen = mWroteLen;
		}
	}

}
