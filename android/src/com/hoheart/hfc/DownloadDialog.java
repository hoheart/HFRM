package com.hoheart.hfc;

import java.io.File;
import java.util.concurrent.ThreadPoolExecutor;

import android.app.Dialog;
import android.content.Context;
import android.os.Environment;
import android.os.Handler;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.ProgressBar;
import android.widget.TextView;

public class DownloadDialog extends Dialog {

	private Downloader mDownloader = new Downloader();
	private Handler mHandler = new Handler();
	private String mDownloadDir = null;
	private String mDownloadPath = null;
	private String mUrl = null;

	private Downloader.DownloadingListener mUserDownloadingListener = null;
	private Downloader.OnCompleteListener mUserOnCompleteListener = null;
	private ThreadPoolExecutor mThreadPool = null;
	private Downloader.DownloadingListener mMyDownloadingListener = new Downloader.DownloadingListener() {

		@Override
		public void downloading(final byte[] buf, final int bufSize,
				final long downloadLen, final long allSize) {
			if (null != mUserDownloadingListener) {
				mUserDownloadingListener.downloading(buf, bufSize, downloadLen,
						allSize);
			}

			setPercent((int) (downloadLen * 100 / allSize));
		}
	};

	private Downloader.OnCompleteListener mMyOnCompleteListener = new Downloader.OnCompleteListener() {

		@Override
		public void onComplete(boolean ret, long size, long allSize,
				final String errStr) {
			if (!ret) {
				mHandler.post(new Runnable() {

					@Override
					public void run() {
						showError();

						TextView vError = (TextView) findViewById(R.id.ErrorStr);
						vError.setText(errStr);

						Button btnOK = (Button) findViewById(R.id.BtnOK);
						btnOK.setText(R.string.Retry);
						btnOK.setOnClickListener(new View.OnClickListener() {

							@Override
							public void onClick(View v) {
								showWaitForDownload();

								startDownload(mThreadPool, mUrl, mDownloadPath);
							}
						});
					}
				});
			} else {
				dismiss();

				if (null != mUserOnCompleteListener) {
					mUserOnCompleteListener.onComplete(ret, size, allSize,
							errStr);
				}
			}
		}
	};

	public DownloadDialog(Context context, boolean cancelable,
			OnCancelListener cancelListener) {
		super(context, cancelable, cancelListener);

		init();
	}

	public DownloadDialog(Context context, int theme) {
		super(context, theme);

		init();
	}

	public DownloadDialog(Context context) {
		super(context);

		init();
	}

	private void init() {
		View decorView = getWindow().getDecorView();
		LayoutInflater li = LayoutInflater.from(getContext());
		try {
			View v = li.inflate(R.layout.download_dialog,
					(ViewGroup) decorView, false);
			setContentView(v);
		} catch (Exception e) {
			e.printStackTrace();
		}

		setTitle(R.string.Download);

		View btnCancel = findViewById(R.id.BtnCancel);
		btnCancel.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				mDownloader.cancel();

				dismiss();
			}
		});

		mDownloadDir = Environment.getExternalStorageDirectory() + "/Download/";
	}

	public void setDownloadingListener(Downloader.DownloadingListener l) {
		mUserDownloadingListener = l;
	}

	public void setOnCompleteListener(Downloader.OnCompleteListener cl) {
		mUserOnCompleteListener = cl;
	}

	public void startDownload(ThreadPoolExecutor tp, String url) {
		File srcFile = new File(url);
		startDownload(tp, url, mDownloadDir + srcFile.getName());
	}

	public void startDownload(ThreadPoolExecutor tp, String url, String savePath) {
		mThreadPool = tp;
		mUrl = url;
		mDownloadPath = savePath;
		mDownloader.addTask(tp, url, savePath, mMyDownloadingListener,
				mMyOnCompleteListener);

		Button btnOK = (Button) findViewById(R.id.BtnOK);
		btnOK.setText(R.string.DownloadBackground);
		btnOK.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				dismiss();
			}
		});
	}

	public String getDownlaodPath() {
		return mDownloadPath;
	}

	private void showWaitForDownload() {
		View w = findViewById(R.id.WaitForDownload);
		w.setVisibility(View.VISIBLE);

		w = findViewById(R.id.error);
		w.setVisibility(View.GONE);

		View d = findViewById(R.id.downloading);
		d.setVisibility(View.GONE);

	}

	private void showDownloading() {
		View w = findViewById(R.id.WaitForDownload);
		w.setVisibility(View.GONE);

		w = findViewById(R.id.error);
		w.setVisibility(View.GONE);

		View d = findViewById(R.id.downloading);
		d.setVisibility(View.VISIBLE);
	}

	private void showError() {
		View w = findViewById(R.id.WaitForDownload);
		w.setVisibility(View.GONE);

		w = findViewById(R.id.error);
		w.setVisibility(View.VISIBLE);

		View d = findViewById(R.id.downloading);
		d.setVisibility(View.GONE);
	}

	public void setPercent(final int percent) {
		final int p;
		if (percent > 100) {
			p = 100;
		} else {
			p = percent;
		}
		mHandler.post(new Runnable() {

			@Override
			public void run() {
				showDownloading();

				ProgressBar b = (ProgressBar) findViewById(R.id.seekBar);
				b.setProgress(p);

				TextView r = (TextView) findViewById(R.id.rate);
				r.setText(p + "%");
			}
		});
	}
}
