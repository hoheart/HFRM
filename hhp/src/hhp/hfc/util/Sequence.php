<?php

namespace Hfc\Util;

/**
 * 用php的文件锁实现的序列。
 *
 * @author Hoheart
 *        
 */
class Sequence {
	
	/**
	 * 存放Sequence文件的文件夹
	 *
	 * @var string
	 */
	protected static $SaveDir = null;

	static public function initSaveDir () {
		// 保证只执行一次。
		if (null === self::$SaveDir) {
			self::$SaveDir = __DIR__ . DIRECTORY_SEPARATOR . 'trigger' . DIRECTORY_SEPARATOR;
		}
	}

	/**
	 * 取得单一实例
	 *
	 * @return \icms\Evaluation\Sequence
	 */
	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new Sequence();
		}
		
		return $me;
	}

	/**
	 * 取得序列的下一个值
	 *
	 * @param string $triggerName        	
	 * @return Ambigous <number, string>
	 */
	public function next ($triggerName) {
		$fpath = self::$SaveDir . $triggerName;
		$fp = fopen($fpath, 'a+');
		flock($fp, LOCK_EX);
		fseek($fp, 0, SEEK_SET);
		$next = fread($fp, 32);
		if (empty($next)) {
			$next = 1;
		}
		
		ftruncate($fp, 0);
		
		fwrite($fp, $next + 1);
		
		flock($fp, LOCK_UN);
		fclose($fp);
		
		return $next;
	}
}

Sequence::initSaveDir();
?>