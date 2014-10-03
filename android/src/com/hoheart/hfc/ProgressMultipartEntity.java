package com.hoheart.hfc;

import java.io.IOException;
import java.io.OutputStream;

import org.apache.http.entity.mime.MultipartEntity;

public class ProgressMultipartEntity extends MultipartEntity {

	private PercentListener mListener = null;

	private int mListenCount = 100;

	protected LengthChangeListener mLengthChangeListener = new LengthChangeListener() {

		@Override
		public void onLengthChanged(int old, int now) {
			if (null == mListener) {
				return;
			}

			float p = (float) (now * 10000 / length) / 100;

			mListener.onPercentChanged(p);
		}
	};

	public void setListener(PercentListener l) {
		mListener = l;
	}

	public void setListenCount(int count) {
		mListenCount = count;
	}

	public long getTotalLength() {
		return length;
	}

	@Override
	public void writeTo(OutputStream outstream) throws IOException {
		HOutputStream o = new HOutputStream(outstream, mLengthChangeListener);
		o.setRate((int)length / mListenCount);

		super.writeTo(o);
	}
}
