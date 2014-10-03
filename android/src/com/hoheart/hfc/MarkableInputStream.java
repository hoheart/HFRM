package com.hoheart.hfc;

import java.io.IOException;
import java.io.InputStream;

public class MarkableInputStream extends InputStream {

	protected InputStream mInputStream = null;
	protected long mReadLimit = -1;
	protected long mReadCount = 0;

	protected boolean mMarkupSupport = true;

	public MarkableInputStream(InputStream is) {
		mInputStream = is;
	}

	public MarkableInputStream(InputStream is, boolean markupSupport) {
		this(is);

		mMarkupSupport = markupSupport;
	}

	@Override
	public void mark(int readlimit) {
		mReadCount = 0;
		mReadLimit = readlimit;
	}

	@Override
	public boolean markSupported() {
		return mMarkupSupport;
	}

	@Override
	public int read() throws IOException {
		int ret = -1;
		if (!reachEnd()) {
			ret = mInputStream.read();
			if (ret > 0) {
				++mReadCount;
			}
		}

		return ret;
	}

	@Override
	public int read(byte[] buffer, int offset, int length) throws IOException {
		int ret = -1;
		if (-1 == mReadLimit) {
			ret = mInputStream.read(buffer, offset, length);
		} else {
			long len = mReadLimit - mReadCount;
			if (0 == len) {
				return -1;
			}

			if (len > length) {
				len = length;
			}
			ret = mInputStream.read(buffer, offset, (int) len);
			if (ret > 0) {
				mReadCount += ret;
			}
		}

		return ret;
	}

	@Override
	public int read(byte[] b) throws IOException {
		int ret = -1;
		if (-1 == mReadLimit) {
			ret = mInputStream.read(b);
		} else {
			return read(b, 0, b.length);
		}

		return ret;
	}

	@Override
	public synchronized void reset() throws IOException {
		if (-1 == mReadLimit) {
			throw new IOException("no mark has set.");
		}

		while (mReadCount < mReadLimit) {
			long l = mInputStream.skip(mReadLimit - mReadCount);
			if (0 == l) {// 有跳不过去的情况，这里最好时读一下，当返回-1时，表示真的没有数据 了
				byte[] tmp = new byte[16];
				l = mInputStream.read(tmp, 0, tmp.length);
				if (-1 == l) {
					mReadCount = mReadLimit;
					break;
				}
			}
			mReadCount += l;
		}
	}

	@Override
	public long skip(long byteCount) throws IOException {
		long ret = -1;
		if (-1 == mReadLimit) {
			ret = mInputStream.skip(byteCount);
		} else {
			long len = mReadLimit - mReadCount;
			if (len < byteCount) {
				len = byteCount;
			}
			ret = mInputStream.skip(len);
			mReadCount += ret;
		}

		return ret;
	}

	public boolean reachEnd() {
		if (-1 == mReadLimit) {
			return false;
		}

		return mReadCount >= mReadLimit;
	}

	public long getReadLen() {
		return mReadCount;
	}

}
