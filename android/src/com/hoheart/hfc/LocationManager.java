package com.hoheart.hfc;

import java.net.URLEncoder;
import java.util.Iterator;
import java.util.LinkedList;
import java.util.List;
import java.util.concurrent.ThreadPoolExecutor;

import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;
import org.json.JSONArray;
import org.json.JSONObject;

import android.content.Context;
import android.location.Location;
import android.location.LocationListener;
import android.os.Bundle;
import android.os.HandlerThread;
import android.telephony.NeighboringCellInfo;
import android.telephony.TelephonyManager;
import android.telephony.gsm.GsmCellLocation;

public class LocationManager {

	public static class GeocodeInfo {

		public String name = "";
		public double lat = -1;
		public double lng = -1;
		public double northeastLat = -1;
		public double northeastLng = -1;
		public double southwestLat = -1;
		public double southwestLng = -1;
	}

	public static interface OnGeocodeRetListener {

		public void onRet(GeocodeInfo i);
	}

	public static class TowerInfo {
		public int mcc;
		public int mnc;
		public int cid;
		public int lac;
		public int rssi;
	}

	private static String mCurrentCityName = "";

	private Location mLocation = null;
	private Object mLocker = new Object();
	private HandlerThread mLooperThread = null;
	private LocationListener mLocationListener = null;

	public LocationManager(Context context, LocationListener l) {
		android.location.LocationManager lm = (android.location.LocationManager) context
				.getSystemService(Context.LOCATION_SERVICE);
		List<String> list = lm.getProviders(true);

		// 如果没有传入listener就另起一个线程监听位置变化。
		if (null == l) {
			if (list.size() > 0) {
				mLooperThread = new HandlerThread("locate_thread");
				mLooperThread.start();
			}

			mLocationListener = new LocationListener() {

				@Override
				public void onLocationChanged(Location location) {
					if (null == mLocation) {
						mLocation = location;

						synchronized (mLocker) {
							try {
								Thread.sleep(30 * 1000);
							} catch (Exception e) {
								e.printStackTrace();
							}
							mLocker.notifyAll();
						}
					} else {
						mLocation.set(location);
					}
				}

				@Override
				public void onProviderDisabled(String provider) {
				}

				@Override
				public void onProviderEnabled(String provider) {
				}

				@Override
				public void onStatusChanged(String provider, int status,
						Bundle extras) {
				}
			};

		} else {
			mLocationListener = l;
		}

		for (Iterator<String> i = list.iterator(); i.hasNext();) {
			String p = i.next();
			if (null != l) {
				lm.requestLocationUpdates(p, -1, 1, mLocationListener);
			} else {
				lm.requestLocationUpdates(p, -1, 1, mLocationListener,
						mLooperThread.getLooper());
			}
		}
	}

	public Location getLocation(long waitTime) {
		try {
			synchronized (mLocker) {
				mLocker.wait(waitTime);
			}
		} catch (Exception e) {
			e.printStackTrace();
		}

		return mLocation;
	}

	public static void postGetCurrentCityName(final Context context) {
		try {
			Thread t = new Thread() {

				@Override
				public void run() {
					try {
						getCurrentCityName(context);
					} catch (Exception e) {
						e.printStackTrace();
					}
				}

			};

			t.start();
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	public static String getCurrentCityName(Context context) {
		mCurrentCityName = getCurrentCityNameByGSM(context);
		if (mCurrentCityName.equals("")) {
			mCurrentCityName = getCurrentCityNameByIP();
		}
		if (mCurrentCityName.equals("")) {
			mCurrentCityName = getCurrentCityNameByGPS(context);
		}

		return mCurrentCityName;
	}

	public static String getCurrentCityNameByGSM(Context context) {
		TelephonyManager telManager = (TelephonyManager) context
				.getSystemService(Context.TELEPHONY_SERVICE);
		GsmCellLocation glc = (GsmCellLocation) telManager.getCellLocation();
		if (null == glc) {
			return "";
		}

		TowerInfo ti = new TowerInfo();
		ti.cid = glc.getCid();
		ti.lac = glc.getLac();
		if (ti.cid < 0 || ti.lac < 0) {
			return "";
		}

		String strOperator = telManager.getNetworkOperator();
		ti.mcc = Integer.valueOf(strOperator.substring(0, 3));// 写入当前城市代码
		ti.mnc = Integer.valueOf(strOperator.substring(3, 5));// 写入网络代码

		List<TowerInfo> tl = new LinkedList<TowerInfo>();
		tl.add(ti);

		List<NeighboringCellInfo> l = telManager.getNeighboringCellInfo();
		for (Iterator<NeighboringCellInfo> i = l.iterator(); i.hasNext();) {
			NeighboringCellInfo cellInfo = i.next();

			TowerInfo ti1 = new TowerInfo();
			ti1.cid = cellInfo.getCid();
			ti1.lac = ti.lac;
			// ti1.lac = cellInfo.getLac();
			ti1.mcc = ti.mcc;
			ti1.mnc = ti.mnc;
			ti1.rssi = cellInfo.getRssi();

			tl.add(ti1);
		}

		return queryLocationFromGoogle(-1, -1, tl);
	}

	public static String getCurrentCityNameByIP() {
		return queryLocationFromGoogle(-1, -1, null);
	}

	public static String queryLocationFromGoogle(double longitude,
			double latitude, List<TowerInfo> tl) {
		String cityName = "";

		try {
			JSONObject jObject = new JSONObject();

			jObject.put("address_language", "zh_CN");
			jObject.put("version", "1.1.0");
			jObject.put("host", "maps.google.com");
			jObject.put("request_address", true);

			if (longitude > 0 && latitude > 0) {
				jObject.put("longitude", longitude);
				jObject.put("latitude", latitude);
			}

			if (null != tl) {
				JSONArray jArray = new JSONArray();
				for (Iterator<TowerInfo> i = tl.iterator(); i.hasNext();) {
					TowerInfo info = i.next();

					JSONObject t = new JSONObject();

					t.put("cell_id", info.cid);
					t.put("location_area_code", info.lac);
					t.put("mobile_country_code", info.mcc);
					t.put("mobile_network_code", info.mnc);
					t.put("signal_strength", info.rssi);

					jArray.put(t);
				}

				jObject.put("cell_towers", jArray);
			}

			// 创建连接，发送请求并接受回应
			DefaultHttpClient client = new DefaultHttpClient();
			HttpPost post = new HttpPost("http://www.google.com/loc/json");
			StringEntity se = new StringEntity(jObject.toString());
			post.setEntity(se);
			HttpResponse resp = client.execute(post);

			String s = Downloader.getHttpString(resp);
			JSONObject ret = new JSONObject(s);
			JSONObject addr = ret.getJSONObject("location").getJSONObject(
					"address");
			cityName = addr.getString("city");
			cityName = cityName.replace("市", "");
		} catch (Exception e) {
			int a = 0;
			++a;
		}

		return cityName;
	}

	public synchronized static String getCurrentCityNameByGPS(Context context) {
		LocationManager lm = new LocationManager(context, null);
		Location l = lm.getLocation(60 * 1000);
		mCurrentCityName = queryLocationFromGoogle(l.getLongitude(),
				l.getLatitude(), null);

		return mCurrentCityName;
	}

	public static void postGeocodeInfo(ThreadPoolExecutor tp,
			final String name, final OnGeocodeRetListener l) {
		tp.submit(new Runnable() {

			@Override
			public void run() {
				GeocodeInfo gi = geocode(name);
				l.onRet(gi);
			}
		});
	}

	public static GeocodeInfo geocode(String name) {
		try {
			String u = new String(name.getBytes("UTF-8"));
			String s = Downloader
					.getHttpString("http://maps.googleapis.com/maps/api/geocode/json?address="
							+ URLEncoder.encode(u)
							+ "&sensor=false&language=zh");

			JSONObject ret = new JSONObject(s);
			if (!ret.optString("status").equals("OK")) {
				return null;
			}

			JSONArray ja = ret.optJSONArray("results");
			int index = -1;
			if (ja.length() > 1) {
				for (int i = 0; i < ja.length(); ++i) {
					// 单个 result
					JSONObject jo = ja.getJSONObject(i);

					boolean isIt = false;

					JSONArray jac = jo.getJSONArray("address_components");
					for (int ii = 0; ii < jac.length(); ++ii) {
						// 单个address_components
						JSONObject joc = jac.getJSONObject(ii);
						if (joc.optString("long_name").equals(name)
								|| joc.optString("short_name").equals(name)) {
							isIt = true;

							break;
						}
					}

					if (isIt) {// 找到result数组里的一个值与其名称相等，就是他了
						index = i;

						break;
					}
				}
			} else if (ja.length() == 1) {
				index = 0;
			}

			if (index >= 0) {
				JSONObject jo = ja.getJSONObject(index);
				JSONObject geometry = jo.getJSONObject("geometry");

				JSONObject l = geometry.getJSONObject("location");
				double lat = l.optDouble("lat");
				double lng = l.optDouble("lng");

				JSONObject viewport = geometry.getJSONObject("viewport");
				JSONObject northeast = viewport.getJSONObject("northeast");
				double nlat = northeast.optDouble("lat");
				double nlng = northeast.optDouble("lng");
				JSONObject southwest = viewport.getJSONObject("southwest");
				double slat = southwest.optDouble("lat");
				double slng = southwest.optDouble("lng");

				GeocodeInfo gi = new GeocodeInfo();
				gi.lat = lat;
				gi.lng = lng;
				gi.northeastLat = nlat;
				gi.northeastLng = nlng;
				gi.southwestLat = slat;
				gi.southwestLng = slng;

				return gi;
			}
		} catch (Exception e) {
			e.printStackTrace();
		}

		return null;
	}
}
