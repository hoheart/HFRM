package com.hoheart.hfc;

import java.io.File;
import java.io.InputStream;
import java.io.StringReader;
import java.security.MessageDigest;
import java.util.Vector;
import java.util.concurrent.ThreadPoolExecutor;

import org.apache.http.util.ByteArrayBuffer;
import org.xmlpull.v1.XmlPullParser;
import org.xmlpull.v1.XmlPullParserFactory;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.Dialog;
import android.content.ComponentName;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.Intent.ShortcutIconResource;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.graphics.drawable.Drawable;
import android.os.Handler;
import android.widget.ImageView;

public class Utile {

	public static String implode(String separator, Vector<String> arr) {
		String ret = "";
		int i = 0;
		for (i = 0; i < arr.size() - 1; ++i) {
			ret += arr.get(i) + separator;
		}

		ret += arr.get(i);

		return ret;
	}

	public static String implode(String separator, String[] arr) {
		String ret = "";
		int i = 0;
		for (i = 0; i < arr.length - 1; ++i) {
			ret += arr[i] + separator;
		}

		ret += arr[i];

		return ret;
	}

	public static boolean delDir(File oldPath) {
		if (oldPath.isDirectory()) {
			File[] files = oldPath.listFiles();
			for (File file : files) {
				if (!delDir(file)) {
					return false;
				}
			}
		}

		if (!oldPath.delete()) {
			return false;
		}

		return true;
	}

	/**
	 * 创建快捷方式
	 */
	public void createShortCut(Context context, Class<?> cls, int appNameRes,
			int appIconName) {
		Intent shortcut = new Intent(
				"com.android.launcher.action.INSTALL_SHORTCUT");
		shortcut.putExtra(Intent.EXTRA_SHORTCUT_NAME,
				context.getString(appNameRes));
		shortcut.putExtra("duplicate", false); // 不允许重复
		ShortcutIconResource iconRes = Intent.ShortcutIconResource.fromContext(
				context, appIconName);// 设置快捷方式的图标
		shortcut.putExtra(Intent.EXTRA_SHORTCUT_ICON_RESOURCE, iconRes);

		// 定义shortcut点击事件
		String action = "com.android.action.test";
		Intent respondIntent = new Intent(context, cls);
		respondIntent.setAction(action);
		shortcut.putExtra(Intent.EXTRA_SHORTCUT_INTENT, respondIntent);

		context.sendBroadcast(shortcut);
	}

	/**
	 * 卸载快捷方式
	 */
	public void deleteShortcut(Context context, Class<?> cls) {
		Intent shortcut = new Intent(
				"com.android.launcher.action.UNINSTALL_SHORTCUT");
		shortcut.putExtra(Intent.EXTRA_SHORTCUT_NAME, "卸载快捷方式"); // 指定要卸载的快捷方式的名称
		String action = "com.android.action.test";
		String appClass = context.getPackageName() + "." + cls;
		ComponentName comp = new ComponentName(context.getPackageName(),
				appClass);
		shortcut.putExtra(Intent.EXTRA_SHORTCUT_INTENT,
				new Intent(action).setComponent(comp));

		context.sendBroadcast(shortcut);
	}

	public static boolean tableExists(SQLiteDatabase db, String tableName) {
		String sql = "SELECT COUNT(*)  as CNT FROM sqlite_master where type='table' and name='"
				+ tableName + "'";
		Cursor ret = db.rawQuery(sql, null);
		ret.moveToFirst();
		boolean isExists = ret.getInt(0) > 0;
		ret.close();

		return isExists;
	}

	public static boolean inArray(String d, String[] arr) {
		if (null == arr) {
			return false;
		}

		for (int i = 0; i < arr.length; ++i) {
			if (arr[i].equals(d)) {
				return true;
			}
		}

		return false;
	}

	public static void loadImageAsync(ThreadPoolExecutor tp,
			final ImageView img, final String url) {
		final Handler h = new Handler();

		tp.submit(new Runnable() {

			@Override
			public void run() {
				final Drawable d = DrawableFactory.createFromHttp(url);
				if (null != d) {
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

	public static String htmlToText(String html) {
		String text = "";
		try {
			XmlPullParserFactory f = XmlPullParserFactory.newInstance();
			XmlPullParser p = f.newPullParser();
			p.setInput(new StringReader(html));
			p.defineEntityReplacementText("&ldquo;", "“");
			p.defineEntityReplacementText("&rdquo;", "”");
			while (p.getEventType() != XmlPullParser.END_DOCUMENT) {
				if (p.getEventType() == XmlPullParser.TEXT) {
					text += p.getText();
				}

				try {
					p.next();
				} catch (Exception e) {
					e.printStackTrace();
				}
			}
		} catch (Exception e) {
			e.printStackTrace();
		}

		if (text.equals("")) {// 什么都没解析出来，一开始就错了，就直接返回text
			text = html;
		}

		return text;
	}

	public static Activity getTopActivity(Activity a) {
		Activity p = a.getParent();
		if (null == p) {
			return a;
		} else {
			return getTopActivity(p);
		}
	}

	public static int readInt(InputStream is, int def) {
		try {
			ByteArrayBuffer buff = readByte(is, 4);
			byte[] b = buff.toByteArray();

			int mask = 0xff;
			int temp = 0;
			int n = 0;
			for (int i = 0; i < 4; i++) {
				n <<= 8;
				temp = b[i] & mask;
				n |= temp;
			}

			return n;
		} catch (Exception e) {
			e.printStackTrace();

			return def;
		}
	}

	/**
	 * 从is中读取指定长度的字节数，一直会读到指定长度时才停止，除非出错。不会多读。
	 * 
	 * @param is
	 * @param b
	 * @param len
	 * @return
	 */
	public static ByteArrayBuffer readByte(InputStream is, int len) {
		ByteArrayBuffer bf = new ByteArrayBuffer(len);

		int count = 0;
		while (count < len) {
			byte[] bt = new byte[len - count];
			int l = 0;
			try {
				l = is.read(bt, 0, len - count);
				if (-1 == l) {
					return null;
				}
			} catch (Exception e) {
				e.printStackTrace();
				return null;
			}
			count += l;

			bf.append(bt, 0, l);
		}

		return bf;
	}

	public static void confirm(Context c, int msgResId,
			DialogInterface.OnClickListener onOkListener,
			DialogInterface.OnClickListener onCancelListener) {
		try {
			Dialog d = new AlertDialog.Builder(c).setTitle(R.string.Prompt)
					.setMessage(msgResId)
					.setPositiveButton(R.string.Confirm, onOkListener)
					.setNegativeButton(R.string.Cancel, onCancelListener)
					.create();

			d.show();
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	public static String md5(String s) {
		byte[] bs = s.getBytes();

		try {
			MessageDigest mdTemp = MessageDigest.getInstance("MD5");
			mdTemp.update(bs);
			byte[] md = mdTemp.digest();
			return getHex(md);
		} catch (Exception e) {
			e.printStackTrace();
		}

		return null;
	}

	public static String getHex(byte[] raw) {
		final String HEXES = "0123456789abcdef";

		if (raw == null) {
			return null;
		}
		final StringBuilder hex = new StringBuilder(2 * raw.length);
		for (final byte b : raw) {
			hex.append(HEXES.charAt((b & 0xF0) >> 4)).append(
					HEXES.charAt((b & 0x0F)));
		}
		return hex.toString();
	}

}
