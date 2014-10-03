package com.hoheart.hfc;

import java.util.Iterator;
import java.util.List;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.content.pm.ApplicationInfo;
import android.content.pm.PackageManager;
import android.content.pm.ResolveInfo;
import android.net.wifi.WifiInfo;
import android.net.wifi.WifiManager;
import android.view.Display;
import android.view.inputmethod.InputMethodManager;

public class OS {
	public static String getMacAddr(Context c) {
		String mac = "";
		WifiManager wifi = (WifiManager) c
				.getSystemService(Context.WIFI_SERVICE);
		if (null != wifi) {
			WifiInfo info = wifi.getConnectionInfo();
			mac = info.getMacAddress();
		}

		return mac;
	}

	public static void hideIME(Activity a) {
		InputMethodManager imm = (InputMethodManager) a
				.getSystemService(Activity.INPUT_METHOD_SERVICE);
		imm.hideSoftInputFromWindow(a.getWindow().getDecorView()
				.getWindowToken(), 0);
	}

	public static ResolveInfo searchSysApp(Context context, String paramString) {
		PackageManager localPackageManager = context.getPackageManager();
		Intent localIntent1 = new Intent(paramString);
		if (paramString.equalsIgnoreCase(Intent.ACTION_GET_CONTENT)) {
			localIntent1.setType("image/jpeg");
		}
		List<ResolveInfo> localList = localPackageManager
				.queryIntentActivities(localIntent1, 0);
		for (Iterator<ResolveInfo> i = localList.iterator(); i.hasNext();) {
			ResolveInfo localResolveInfo = i.next();

			int appFlag = localResolveInfo.activityInfo.applicationInfo.flags;
			if ((appFlag & ApplicationInfo.FLAG_SYSTEM) > 0) {
				return localResolveInfo;
			}
		}

		return null;
	}

	static public Size getScreenWidth(Activity a) {
		Display display = a.getWindowManager().getDefaultDisplay();

		Size s = new Size();
		s.height = display.getHeight();
		s.width = display.getWidth();

		return s;
	}
}
