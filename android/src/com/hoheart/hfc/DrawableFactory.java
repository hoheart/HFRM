package com.hoheart.hfc;

import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;

import org.apache.http.HttpResponse;
import org.apache.http.HttpStatus;
import org.apache.http.StatusLine;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;

import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.graphics.PixelFormat;
import android.graphics.drawable.Drawable;

public class DrawableFactory {

	public static Drawable createFromHttp(String uri) {
		Drawable d = null;

		DefaultHttpClient httpclient = new DefaultHttpClient();
		HttpGet httpget = new HttpGet(uri);

		try {
			HttpResponse resp = httpclient.execute(httpget);

			StatusLine statusLine = resp.getStatusLine();
			if (null == statusLine) {
				return d;
			}
			if (HttpStatus.SC_OK != statusLine.getStatusCode()) {
				return d;
			}
			InputStream is = resp.getEntity().getContent();
			if (null == is) {
				return d;
			}

			d = Drawable.createFromStream(is, null);
		} catch (Exception e) {
			return d;
		}

		return d;
	}

	public static Drawable createFromHttp(HttpClient httpClient, String uri) {
		Drawable d = null;

		HttpGet httpget = new HttpGet(uri);

		try {
			HttpResponse resp = httpClient.execute(httpget);

			StatusLine statusLine = resp.getStatusLine();
			if (null == statusLine) {
				return d;
			}
			if (HttpStatus.SC_OK != statusLine.getStatusCode()) {
				return d;
			}
			InputStream is = resp.getEntity().getContent();
			if (null == is) {
				return d;
			}

			d = Drawable.createFromStream(is, null);
		} catch (Exception e) {
			return d;
		}

		return d;
	}

	public static boolean saveDrawable(Drawable d,
			Bitmap.CompressFormat format, String path) {
		try {
			Canvas c = new Canvas();
			Bitmap b = Bitmap
					.createBitmap(
							d.getIntrinsicWidth(),
							d.getIntrinsicHeight(),
							d.getOpacity() != PixelFormat.OPAQUE ? Bitmap.Config.ARGB_8888
									: Bitmap.Config.RGB_565);
			c.setBitmap(b);
			d.draw(c);

			File f = new File(path);
			FileOutputStream fo = new FileOutputStream(f);
			b.compress(format, 62, fo);

			return true;
		} catch (Exception e) {
			e.printStackTrace();
		}

		return false;
	}
}
