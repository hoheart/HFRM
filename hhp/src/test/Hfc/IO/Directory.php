<?php

namespace test\Hfc\IO;

use Hfc\IO\Directory as HDirectory;
use Hfc\IO\File as HFile;
use Hfc\Exception\SystemAPIErrorException;

class Directory {

	public function test () {
		if (! $this->cycle() || ! $this->release() || ! $this->unlink() || ! $this->createAll() ||
				 ! $this->clear()) {
			return false;
		}
		
		return true;
	}

	/**
	 * 测试循环
	 */
	public function cycle () {
		$dpath = __DIR__ . DIRECTORY_SEPARATOR . 'test_cycle' . DIRECTORY_SEPARATOR;
		$dir = new HDirectory($dpath);
		
		$arr = array(
			'a.txt',
			'b.txt'
		);
		try {
			$dir->create();
		} catch (\Exception $e) {
		}
		
		foreach ($arr as $name) {
			try {
				HFile::SCreate($dpath . $name);
			} catch (\Exception $e) {
			}
		}
		
		$readArr = array();
		foreach ($dir as $name) {
			$readArr[] = $name;
		}
		
		if ($arr != $readArr) {
			return false;
		}
		
		$dir->unlink(true);
		
		return true;
	}

	public function release () {
		$dpath = __DIR__ . DIRECTORY_SEPARATOR . 'test_cycle' . DIRECTORY_SEPARATOR;
		$dir = new HDirectory($dpath);
		
		try {
			$dir->create();
		} catch (\Exception $e) {
		}
		
		foreach ($dir as $name) {
			// 这样就打开了文件夹，下面的删除操作不管怎么样（根据操作系统的不同，可能现象不一样），
			// 文件夹都应该存在，但不能用file_exists来判断，只能新建，根据是否成功来判断。
		}
		try {
			HDirectory::SUnlink($dpath, true);
		} catch (\Exception $e) {
			if (! $e instanceof SystemAPIErrorException) {
				return false;
			}
		}
		
		$createSuc = false;
		try {
			HDirectory::SCreate($dpath);
			$createSuc = true;
		} catch (\Exception $e) {
		}
		if ($createSuc) {
			return false;
		}
		
		unset($dir);
		
		try {
			HDirectory::SUnlink($dpath, true);
		} catch (\Exception $e) {
		}
		
		return true;
	}

	public function unlink () {
		try {
			$dir = __DIR__ . DIRECTORY_SEPARATOR . 'aa' . DIRECTORY_SEPARATOR;
			HDirectory::SCreate($dir);
		} catch (\Exception $e) {
		}
		try {
			$sub = $dir . 'sub' . DIRECTORY_SEPARATOR;
			HDirectory::SCreate($sub);
		} catch (\Exception $e) {
		}
		try {
			$file = $sub . 'a.txt';
			HFile::SCreate($file);
		} catch (\Exception $e) {
		}
		
		// 检查创建成功没
		if (! HFile::SIsDir($sub) || ! HFile::SIsFile($file)) {
			return false;
		}
		
		$d = new HDirectory($dir);
		$d->unlink(true);
		
		if (HFile::SExists($sub)) {
			return false;
		}
		
		return true;
	}

	public function createAll () {
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'bb' . DIRECTORY_SEPARATOR . 'aa';
		$dir = new HDirectory($path);
		$dir->createAll();
		
		if (! HDirectory::SExists($path)) {
			return false;
		}
		
		HDirectory::SUnlink(__DIR__ . DIRECTORY_SEPARATOR . 'bb', true);
		
		return true;
	}

	public function clear () {
		try {
			$dir = __DIR__ . DIRECTORY_SEPARATOR . 'aa' . DIRECTORY_SEPARATOR;
			HDirectory::SCreate($dir);
		} catch (\Exception $e) {
		}
		try {
			$sub = $dir . 'sub' . DIRECTORY_SEPARATOR;
			HDirectory::SCreate($sub);
		} catch (\Exception $e) {
		}
		try {
			$file = $sub . 'a.txt';
			HFile::SCreate($file);
		} catch (\Exception $e) {
		}
		
		// 检查创建成功没
		if (! HFile::SIsDir($sub) || ! HFile::SIsFile($file)) {
			return false;
		}
		
		$d = new HDirectory($dir);
		$d->clear();
		
		if (HDirectory::SExists($sub)) {
			return false;
		}
		
		$d->unlink();
		
		return true;
	}
}
?>