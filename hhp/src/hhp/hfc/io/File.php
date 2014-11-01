<?php

namespace hfc\io;

use hfc\exception\SystemAPIErrorException;

class File extends Path {
	
	/**
	 * 常量。打开文件是读还是写。
	 *
	 * @var integer
	 */
	const OPEN_MODE_NONE = - 1; // 没有打开
	const OPEN_MODE_READ = 1;
	const OPEN_MODE_WRITE = 2;
	
	/**
	 * 当前的打开文件的方式。
	 *
	 * @var integer
	 */
	protected $mOpenMode = self::OPEN_MODE_NONE;

	static public function SUnlink ($path, $includeSub = false) {
		if (! unlink($path)) {
			throw new SystemAPIErrorException('can not unlink file:' . $path);
		}
	}

	public function unlink ($includeSub = false) {
		$this->release();
		self::SUnlink($this->mPath);
	}

	static public function SCreate ($path) {
		$fp = fopen($path, 'a+');
		if (false === $fp) {
			throw new SystemAPIErrorException('can not create file:' . $path);
		}
		
		fclose($fp);
	}

	public function create () {
		self::SCreate($this->mPath);
	}

	static public function SSize ($path) {
		clearstatcache(true, $path);
		$size = filesize($path);
		if (false === $size) {
			throw new SystemAPIErrorException('can not get size of file: ' . $path);
		}
		
		return $size;
	}

	public function size () {
		return self::SSize($this->mPath);
	}

	protected function release () {
	}
}
?>