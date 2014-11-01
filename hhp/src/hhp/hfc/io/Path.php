<?php

namespace hfc\io;

use hfc\io\Directory;
use hfc\io\File;
use hfc\io\FileNotFoundException;

abstract class Path {
	
	/**
	 * 文件系统路径
	 *
	 * @var string
	 */
	protected $mPath = null;

	public function __construct ($path) {
		$this->mPath = $path;
	}

	public function __destruct () {
		$this->release();
	}

	/**
	 * 根据传入路径是文件夹还是文件，实例化File或Directory对象。
	 *
	 * @param string $path        	
	 */
	static public function Attach ($path) {
		$obj = null;
		
		if (self::SIsFile($path)) {
			$obj = new File($path);
		} else if (self::SIsDir($path)) {
			$obj = new Directory($path);
		} else {
			throw new FileNotFoundException('can not found file:' . $path);
		}
		
		return $obj;
	}

	static public function SIsFile ($path) {
		return is_file($path);
	}

	public function isFile () {
		return self::SIsFile($this->mPath);
	}

	static public function SIsDir ($path) {
		return is_dir($path);
	}

	public function isDir () {
		return self::SIsDir($this->mPath);
	}

	static public function SExists ($path) {
		return file_exists($path);
	}

	public function exists () {
		return self::SExists($this->mPath);
	}

	/**
	 * 删除指定路径的文件或文件夹。
	 *
	 * @param string $path        	
	 * @param string $includeSub
	 *        	对于File，此次参数无效。
	 */
	abstract static public function SUnlink ($path, $includeSub = false);

	/**
	 * 删除文件或文件夹
	 *
	 * @param string $includeSub
	 *        	对于File，此次参数无效。
	 */
	abstract public function unlink ($includeSub = false);

	abstract static public function SCreate ($path);

	abstract public function create ();

	abstract protected function release ();
}
?>