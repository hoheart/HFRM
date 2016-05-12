<?php

namespace test\HfcTest\IOTest;

use hfc\io\Path as HPath;
use hfc\io\Directory as HDirectory;
use hfc\io\File as HFile;
use hfc\io\FileNotFoundException;
use test\AbstractTest;

class PathTest extends AbstractTest {

	public function test () {
		$this->attach();
		$this->isFile();
		$this->isDir();
		$this->exists();
	}

	public function attach () {
		$p = HPath::Attach(__DIR__);
		if (! $p instanceof HDirectory) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$p = HPath::Attach(__FILE__);
		if (! $p instanceof HFile) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		try {
			HPath::Attach('/asdf/asdf');
		} catch (\Exception $e) {
			if (! $e instanceof FileNotFoundException) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}
	}

	public function isFile () {
		$p = HPath::Attach(__DIR__);
		if ($p->isFile()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$p = HPath::Attach(__FILE__);
		if (! $p->isFile()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function isDir () {
		$p = HPath::Attach(__FILE__);
		if ($p->isDir()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$p = HPath::Attach(__DIR__);
		if (! $p->isDir()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function exists () {
		$file = __DIR__ . DIRECTORY_SEPARATOR . 'a.txt';
		$f = new HFile($file);
		try {
			$f->create();
		} catch (\Exception $e) {
		}
		
		$p = HPath::Attach($file);
		if (! $p->exists()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$f->unlink();
		
		if ($p->exists()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>