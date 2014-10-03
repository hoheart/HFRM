package com.hoheart.hfc;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.util.zip.ZipEntry;
import java.util.zip.ZipInputStream;

import org.apache.http.protocol.HTTP;
import org.apache.http.util.ByteArrayBuffer;

public class Zip {

	public static int unzipFile(String zipFilePath, String targetPath,
			String[] escapeNameArr) {
		InputStream is = null;
		try {
			File zipFile = new File(zipFilePath);
			is = new FileInputStream(zipFile);
		} catch (Exception e) {
			return -1;
		}

		return unzipFile(is, targetPath, escapeNameArr);
	}

	/**
	 * 解压zip文件
	 * 
	 * @param zipFilePath
	 *            zip源文件
	 * @param targetPath
	 *            目标文件夹
	 * @param escapNameArr
	 *            要跳过的文件，这些文件将不被解压。
	 * 
	 * @return >0:全部成功，==0：部分成功，<0：全部失败
	 */
	public static int unzipFile(InputStream is, String targetPath,
			String[] escapeNameArr) {
		boolean hadFailed = false;
		boolean hadSuc = false;

		try {
			ZipInputStream zis = new ZipInputStream(is);
			ZipEntry entry = null;

			while ((entry = zis.getNextEntry()) != null) {
				String zipPath = entry.getName().replace("\\", File.separator);
				if (Utile.inArray(zipPath, escapeNameArr)) {
					continue;
				}

				try {
					if (entry.isDirectory()) {
						File zipFolder = new File(targetPath + File.separator
								+ zipPath);
						if (!zipFolder.exists()) {
							zipFolder.mkdirs();
						}
					} else {
						File file = new File(targetPath + File.separator
								+ zipPath);
						if (!file.exists()) {
							File pathDir = file.getParentFile();
							if (!pathDir.exists()) {
								pathDir.mkdirs();
							}
							file.createNewFile();
						} else {
							file.delete();// 如果不删除，有的android版本可能会报没权限错。
						}
						FileOutputStream fos = new FileOutputStream(file);
						final int bufferSize = (int) entry.getSize();
						byte[] buffer = new byte[bufferSize];
						int count = 0;

						while (true) {
							int l = zis.read(buffer, 0, bufferSize);
							if (-1 == l) {
								break;
							}

							count += l;

							fos.write(buffer, 0, l);

							if (count == bufferSize) {
								break;
							}
						}
						fos.close();
					}
				} catch (Exception e) {
					hadFailed = true;
					continue;
				}

				hadSuc = true;
			}
			zis.close();
		} catch (Exception e) {
			return -1;
		}

		if (hadSuc && hadFailed) {
			return 0;
		} else if (hadSuc && !hadFailed) {
			return 1;
		} else {
			return -1;
		}
	}

	public static String getTextFile(InputStream is, String fileName) {
		String data = null;

		ZipInputStream zs = new ZipInputStream(is);
		// 解压data.json文件
		try {

			ZipEntry entry = null;
			while ((entry = zs.getNextEntry()) != null) {
				if (entry.isDirectory()) {
					continue;
				}

				String zipPath = entry.getName().replace("\\", File.separator);
				if (!zipPath.equals(fileName)) {
					continue;
				}

				ByteArrayBuffer db = new ByteArrayBuffer(0);
				int s = (int) entry.getSize();
				byte[] buffer = new byte[s];
				int count = 0;
				while (true) {
					int len = zs.read(buffer, 0, s);
					if (-1 == len) {
						break;
					}

					count += len;

					db.append(buffer, 0, len);

					if (count == s) {
						break;
					}
				}

				data = new String(db.toByteArray(), 0, db.length(), HTTP.UTF_8);
			}
		} catch (Exception e) {
			e.printStackTrace();
		}

		return data;
	}
}
