package com.hoheart.hfc;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.util.List;
import java.util.concurrent.ThreadPoolExecutor;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.HttpStatus;
import org.apache.http.NameValuePair;
import org.apache.http.StatusLine;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.HTTP;
import org.apache.http.util.ByteArrayBuffer;

public class Downloader {

	public static interface DownloadingListener {

		public void downloading(byte[] buf, int bufLen, long downloadLen,
				long allSize);
	}

	public static interface OnCompleteListener {

		public void onComplete(boolean ret, long size, long allSize,
				String errStr);
	}

	public static interface OnStringRetListener {

		public void onStringRet(String str);
	}

	private boolean mCanceled = false;

	public InputStream download(final HttpClient httpClient, final String url) {
		long[] fileLen = new long[1];
		return download(httpClient, url, fileLen);
	}

	public InputStream download(final HttpClient httpClient, final String url,
			long[] fileLen) {
		try {
			HttpGet httpget = new HttpGet(url);

			HttpResponse resp = httpClient.execute(httpget);
			if (mCanceled) {
				return null;
			}

			StatusLine statusLine = resp.getStatusLine();
			if (null == statusLine) {
				throw new Exception("download no status line.");
			}
			if (HttpStatus.SC_OK != statusLine.getStatusCode()) {
				throw new Exception("status code not ok.");
			}

			fileLen[0] = resp.getEntity().getContentLength();
			if (0 == fileLen[0]) {
				return null;
			}

			InputStream is = resp.getEntity().getContent();
			if (null == is) {
				throw new Exception("stream is null.");
			}

			return is;
		} catch (Exception e) {
			e.printStackTrace();
		}

		return null;
	}

	public void addTask(ThreadPoolExecutor tp, String url, String destPath,
			DownloadingListener l, OnCompleteListener cl) {
		DefaultHttpClient dhc = new DefaultHttpClient();
		addTask(tp, dhc, url, new File(destPath), l, cl);
	}

	public File addTask(ThreadPoolExecutor tp, HttpClient hc, String url,
			DownloadingListener l, OnCompleteListener cl) throws IOException {
		File tmpFile = File.createTempFile("", null);
		addTask(tp, hc, url, tmpFile, l, cl);

		return tmpFile;
	}

	public void addTask(ThreadPoolExecutor tp, final HttpClient httpClient,
			final String url, final File destFile, final DownloadingListener l,
			final OnCompleteListener cl) {
		mCanceled = false;

		try {
			tp.submit(new Runnable() {

				@Override
				public void run() {
					int downloadLen = 0;
					long[] flen = { -1 };

					try {
						InputStream is = download(httpClient, url, flen);

						FileOutputStream fo = null;
						if (null != destFile) {
							destFile.delete();
							destFile.getParentFile().mkdirs();
							destFile.createNewFile();
							if (destFile.isFile()) {
								fo = new FileOutputStream(destFile);
							}
						}

						byte[] b = new byte[1024];
						while (!mCanceled) {
							int len = is.read(b, 0, b.length);
							if (-1 == len) {
								cl.onComplete(downloadLen == flen[0],
										downloadLen, flen[0], null);

								return;
							} else {
								downloadLen += len;

								if (null != fo) {
									fo.write(b, 0, len);
								}

								l.downloading(b, len, downloadLen, flen[0]);

								if (downloadLen == flen[0]) {
									cl.onComplete(true, downloadLen, flen[0],
											null);

									return;
								}
							}
						}
					} catch (Throwable e) {
						e.printStackTrace();

						cl.onComplete(false, downloadLen, flen[0],
								e.getMessage());

						return;
					}
				}
			});
		} catch (Exception e) {
			cl.onComplete(false, 0, 0, e.getMessage());
		}
	}

	public void cancel() {
		mCanceled = true;
	}

	public static void postGetHttpString(final HttpClient httpClient,
			final String url, final OnStringRetListener l) {
		Thread t = new Thread() {

			@Override
			public void run() {
				String str = getHttpString(httpClient, url);
				if (null != l) {
					l.onStringRet(str);
				}
			}
		};
		t.start();
	}

	/**
	 * 把输入流转换成字符数组
	 * 
	 * @param inputStream
	 *            输入流
	 * @return 字符数组
	 * @throws Exception
	 */
	public static String getHttpString(String urlstr) {
		DefaultHttpClient httpclient = new DefaultHttpClient();
		return getHttpString(httpclient, urlstr);
	}

	/**
	 * 把输入流转换成字符数组
	 * 
	 * @param inputStream
	 *            输入流
	 * @return 字符数组
	 * @throws Exception
	 */
	public static String getHttpString(HttpClient httpclient, String urlstr) {
		HttpGet httpget = new HttpGet(urlstr);

		String str = "";

		try {
			HttpResponse resp = httpclient.execute(httpget);

			str = getHttpString(resp);

		} catch (Exception e) {
			return str;
		}

		return str;
	}

	public static String getHttpString(HttpClient httpclient, String urlstr,
			List<NameValuePair> valArr) {
		String str = "";

		HttpPost post = new HttpPost(urlstr);

		try {
			UrlEncodedFormEntity content = new UrlEncodedFormEntity(valArr,
					HTTP.UTF_8);
			post.setEntity(content);

			HttpResponse resp = httpclient.execute(post);

			str = getHttpString(resp);

		} catch (Exception e) {
			return str;
		}

		return str;
	}

	public static String getHttpString(HttpClient httpclient, HttpPost post) {
		String str = "";

		try {
			HttpResponse resp = httpclient.execute(post);

			str = getHttpString(resp);
		} catch (Exception e) {
			return str;
		}

		return str;
	}

	public static String getHttpString(String urlstr, List<NameValuePair> valArr) {
		DefaultHttpClient httpclient = new DefaultHttpClient();
		return getHttpString(httpclient, urlstr, valArr);
	}

	public static String getHttpString(HttpResponse resp) {
		String str = "";

		try {
			StatusLine statusLine = resp.getStatusLine();
			if (null == statusLine) {
				return str;
			}
			if (HttpStatus.SC_OK != statusLine.getStatusCode()) {
				return str;
			}

			HttpEntity entity = resp.getEntity();
			InputStream is = entity.getContent();
			if (null == is) {
				return str;
			}

			// 如果是chunked的数据，entity的contentlength是无效的，这时只能循环读
			byte[] b = new byte[10240];
			// 循环读的时候，因为每次只读b.length个字节，所以超过这个长度时，一个中文字符可能就被折断了，
			// 要转成unicode就出错了。所以，只有读完了一起转才对。
			ByteArrayBuffer total = new ByteArrayBuffer(0);
			while (true) {
				int len = is.read(b, 0, b.length);
				if (len < 0) {
					break;
				}

				total.append(b, 0, len);
			}

			str = new String(total.buffer(), 0, total.length(), HTTP.UTF_8);
		} catch (Exception e) {
			return str;
		}

		return str;
	}
}
