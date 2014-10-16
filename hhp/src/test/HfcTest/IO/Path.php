<?php

namespace test\Hfc\IO;

use Hfc\IO\Path as HPath;
use Hfc\IO\Directory as HDirectory;
use Hfc\IO\File as HFile;
use Hfc\IO\FileNotFoundException;

class Path {

	public function test () {
		if (! $this->attach() || ! $this->isFile() || ! $this->isDir() || ! $this->exists()) {
			return false;
		}
		
		return true;
	}

	public function attach () {
		$p = HPath::Attach(__DIR__);
		if (! $p instanceof HDirectory) {
			return false;
		}
		
		$p = HPath::Attach(__FILE__);
		if (! $p instanceof HFile) {
			return false;
		}
		
		try {
			HPath::Attach('/asdf/asdf');
		} catch (\Exception $e) {
			if (! $e instanceof FileNotFoundException) {
				return false;
			}
		}
		
		return true;
	}

	public function isFile () {
		$p = HPath::Attach(__DIR__);
		if ($p->isFile()) {
			return false;
		}
		
		$p = HPath::Attach(__FILE__);
		if (! $p->isFile()) {
			return false;
		}
		
		return true;
	}

	public function isDir () {
		$p = HPath::Attach(__FILE__);
		if ($p->isDir()) {
			return false;
		}
		
		$p = HPath::Attach(__DIR__);
		if (! $p->isDir()) {
			return false;
		}
		
		return true;
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
			return false;
		}
		
		$f->unlink();
		
		if ($p->exists()) {
			return false;
		}
		
		return true;
	}
}
?>