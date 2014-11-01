<?php

namespace hfc\io;

use hfc\exception\SystemAPIErrorException;

/**
 * 文件目录操作函数。可用于foreach语句。
 *
 * @author Hoheart
 *        
 */
class Directory extends Path implements \Iterator {
	
	/**
	 * 文件指针
	 *
	 * @var resource
	 */
	protected $mDirPointer = null;
	protected $mCurrentName = null;
	protected $mIndex = - 1;
	
	// Iterator接口执行顺序rewind/valid/current/key/next/valid/current/key......
	public function current () {
		return $this->mCurrentName;
	}

	public function key () {
		return $this->mIndex;
	}

	public function next () {
		$this->mCurrentName = $this->readDir();
		++ $this->mIndex;
	}

	public function rewind () {
		$this->release();
		
		$this->mDirPointer = null;
		$this->mIndex = - 1;
		$this->mCurrentName = null;
		
		$this->openDir();
		
		$this->next();
	}

	public function valid () {
		if (false === $this->mCurrentName) {
			return false;
		} else {
			return true;
		}
	}

	protected function openDir () {
		if (null == $this->mDirPointer) {
			$this->mDirPointer = opendir($this->mPath);
			if (false === $this->mDirPointer) {
				throw new SystemAPIErrorException('can not open dir:' . $this->mPath);
			}
		}
	}

	protected function release () {
		if (null != $this->mDirPointer) {
			closedir($this->mDirPointer);
			
			$this->mDirPointer = null;
		}
	}

	/**
	 * 读取文件夹中的条目，返回相对于文件夹的名称。
	 *
	 * @return string
	 */
	protected function readDir () {
		$name = readdir($this->mDirPointer);
		if ('.' == $name || '..' == $name) {
			$name = $this->readDir();
		}
		
		return $name;
	}

	/**
	 * 删除文件夹。注意，有的系统虽然因为在别处打开了文件夹导致不能及时删除，但不会报错，
	 * 当所有打开操作都关闭时 ，文件夹就会真证被删除。
	 *
	 * @param string $dir        	
	 * @param string $includeSub        	
	 * @throws SystemAPIErrorException
	 */
	static function SUnlink ($dir, $includeSub = false) {
		if ($includeSub) {
			$d = new Directory($dir);
			foreach ($d as $name) {
				$entity = self::Attach($dir . DIRECTORY_SEPARATOR . $name);
				$entity->unlink($includeSub);
			}
			
			$d->unlink();
		} else {
			if (! rmdir($dir)) {
				throw new SystemAPIErrorException('can not rmdir:' . $dir . '. may be not empty.');
			}
		}
	}

	public function unlink ($includeSub = false) {
		$this->release();
		self::SUnlink($this->mPath, $includeSub);
	}

	static public function SCreate ($path) {
		if (! mkdir($path)) {
			throw new SystemAPIErrorException('can not mkdir :' . $path);
		}
	}

	public function create () {
		self::SCreate($this->mPath);
	}

	static public function SCreateAll ($path) {
		$dirArr = array(
			$path
		);
		
		$dir = null;
		while (! self::SExists($dir = dirname($path))) {
			$dirArr[] = $dir;
			$path = $dir;
		}
		
		for ($i = count($dirArr) - 1; $i >= 0; -- $i) {
			self::SCreate($dirArr[$i]);
		}
	}

	public function createAll () {
		self::SCreateAll($this->mPath);
	}

	static public function SClear ($dir) {
		$d = new Directory($dir);
		foreach ($d as $name) {
			$entity = self::Attach($dir . DIRECTORY_SEPARATOR . $name);
			$entity->unlink(true);
		}
	}

	public function clear () {
		self::SClear($this->mPath);
	}
}
?>