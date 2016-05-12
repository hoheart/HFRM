<?php

namespace test\HfcTest\IOTest;

use hfc\io\File as HFile;
use test\AbstractTest;

class FileTest extends AbstractTest {

	public function test () {
		$this->unlink();
		$this->create();
		$this->size();
	}

	public function unlink () {
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'a.txt';
		$f = new HFile($path);
		$f->create();
		
		if (! $f->exists()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$f->unlink();
		
		if ($f->exists()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function create () {
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'a.txt';
		$f = new HFile($path);
		$f->create();
		
		if (! $f->exists()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$f->unlink();
	}

	public function size () {
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'a.txt';
		
		$fp = fopen($path, 'a+');
		fwrite($fp, 'abc');
		fclose($fp);
		
		$f = new HFile($path);
		if ($f->size() !== filesize($path)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$f->unlink();
	}
}
?>