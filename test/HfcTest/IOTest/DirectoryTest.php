<?php

namespace test\HfcTest\IOTest;

use hfc\io\Directory as HDirectory;
use hfc\io\File as HFile;
use test\AbstractTest;

class DirectoryTest extends AbstractTest {

	public function test () {
		$this->iterator();
		$this->unlink();
		$this->createAll();
		$this->clear();
	}

	/**
	 * 测试迭代器
	 */
	public function iterator () {
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
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dir->unlink(true);
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
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$d = new HDirectory($dir);
		$d->unlink(true);
		
		if (HFile::SExists($sub)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function createAll () {
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'bb' . DIRECTORY_SEPARATOR . 'aa';
		$dir = new HDirectory($path);
		$dir->createAll();
		
		if (! HDirectory::SExists($path)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		HDirectory::SUnlink(__DIR__ . DIRECTORY_SEPARATOR . 'bb', true);
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
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$d = new HDirectory($dir);
		$d->clear();
		
		if (HDirectory::SExists($sub)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$d->unlink();
	}
}
?>