<?php

namespace test\Hfc\IO;

use Hfc\IO\Directory as HDirectory;
use Hfc\IO\File as HFile;
use Hfc\Exception\SystemAPIErrorException;

class File {

	public function test () {
		if (! $this->unlink() || ! $this->create() || ! $this->size()) {
			return false;
		}
		
		return true;
	}

	public function unlink () {
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'a.txt';
		$f = new HFile($path);
		$f->create();
		
		if (! $f->exists()) {
			return false;
		}
		
		$f->unlink();
		
		if ($f->exists()) {
			return false;
		}
		
		return true;
	}

	public function create () {
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'a.txt';
		$f = new HFile($path);
		$f->create();
		
		if (! $f->exists()) {
			return false;
		}
		
		$f->unlink();
		
		return true;
	}

	public function size () {
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'a.txt';
		
		$fp = fopen($path, 'a+');
		fwrite($fp, 'abc');
		fclose($fp);
		
		$f = new HFile($path);
		if ($f->size() !== filesize($path)) {
			return false;
		}
		
		$f->unlink();
		
		return true;
	}
}
?>