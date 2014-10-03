package com.hoheart.hfc;

import java.io.FileOutputStream;
import java.io.InputStream;
import java.util.concurrent.ThreadPoolExecutor;

import org.apache.http.HttpResponse;
import org.apache.http.HttpStatus;
import org.apache.http.StatusLine;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Matrix;
import android.graphics.drawable.Drawable;
import android.os.Handler;
import android.widget.ImageView;

public class ImageFactory {

	/**
	 * 从HTTP服务器取图片
	 * 
	 * @param url
	 * @return
	 */
	public static Bitmap decodeHttpStream(String uri) {
		Bitmap bmp = null;

		DefaultHttpClient httpclient = new DefaultHttpClient();
		HttpGet httpget = new HttpGet(uri);

		try {
			HttpResponse resp = httpclient.execute(httpget);

			StatusLine statusLine = resp.getStatusLine();
			if (null == statusLine) {
				return bmp;
			}
			if (HttpStatus.SC_OK != statusLine.getStatusCode()) {
				return bmp;
			}
			InputStream is = resp.getEntity().getContent();
			if (null == is) {
				return bmp;
			}

			bmp = BitmapFactory.decodeStream(is);
		} catch (Exception e) {
			return bmp;
		}

		return bmp;
	}

	public static void resizeImage(String src, String dest, int newWidth,
			int newHeight) {
		Bitmap s = resizeImage(BitmapFactory.decodeFile(src), newWidth,
				newHeight);
		FileOutputStream fos;
		try {
			fos = new FileOutputStream(dest);
			s.compress(Bitmap.CompressFormat.JPEG, 60, fos);
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	/**
	 * 图片的缩放方法
	 * 
	 * @param bgimage
	 *            ：源图片资源
	 * @param newWidth
	 *            ：缩放后宽度
	 * @param newHeight
	 *            ：缩放后高度
	 * @return Bitmap
	 */
	public static Bitmap resizeImage(Bitmap bgimage, int newWidth, int newHeight) {
		if (null == bgimage) {
			return null;
		}

		// 获取这个图片的宽和高
		int width = bgimage.getWidth();
		int height = bgimage.getHeight();

		// 创建操作图片用的matrix对象
		Matrix matrix = new Matrix();

		// 计算缩放率，新尺寸除原始尺寸
		float scaleWidth = ((float) newWidth) / width;
		float scaleHeight = ((float) newHeight) / height;

		// 缩放图片动作

		matrix.postScale(scaleWidth, scaleHeight);
		Bitmap bitmap = Bitmap.createBitmap(bgimage, 0, 0, width, height,
				matrix, true);

		return bitmap;
	}

	public static Bitmap getZoomedBmp(String path, int width, int height) {
		// 有的图片过大，载入内存会报java.lang.OutOfMemoryError: bitmap size exceeds VM
		// budget
		try {
			BitmapFactory.Options opt = new BitmapFactory.Options();
			opt.inJustDecodeBounds = true;// 只取得图片的尺寸
			BitmapFactory.decodeFile(path, opt);
			int w = opt.outWidth;
			int h = opt.outHeight;

			int wSize = w / width;
			int hSize = h / height;

			int biggerSize = wSize > hSize ? wSize : hSize;
			// 重新解码图片
			opt.inJustDecodeBounds = false;
			opt.inSampleSize = biggerSize;

			Bitmap bmp = BitmapFactory.decodeFile(path, opt);
			return bmp;
		} catch (Throwable e) {
			e.printStackTrace();
		}

		return null;
	}

	public static void loadImageAsync(ThreadPoolExecutor tp,
			final ImageView img, final String url) {
		synchronized (img) {
			Object t = img.getTag();
			if (null != t) {
				String u = (String) t;
				if (u.equals(url)) {
					return;
				}
			}

			img.setTag(url);
			img.setImageDrawable(null);
		}

		final Handler h = new Handler();

		tp.submit(new Runnable() {

			@Override
			public void run() {
				final Drawable d = DrawableFactory.createFromHttp(url);

				synchronized (img) {
					Object t = img.getTag();
					if (null != t) {
						String u = (String) t;
						if (!u.equals(url)) {
							return;
						}
					}

					h.post(new Runnable() {

						@Override
						public void run() {
							img.setImageDrawable(d);
						}
					});
				}
			}
		});
	}
}
