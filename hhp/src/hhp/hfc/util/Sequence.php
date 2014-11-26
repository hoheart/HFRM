<?php

namespace hfc\util;

use hfc\exception\ParameterErrorException;

/**
 * 用php的文件锁实现的序列。
 * 因为系统中不可能给同一个名字返回两个相同的序列，所以，设计成单实例，且只能在实例化时指定文件夹，因为文件夹一变，序列又归零了。
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
	protected $mSaveDir = null;

	protected function __construct () {
	}

	/**
	 * 取得单一实例。
	 * 为什么设计成单实例，参见类的说明。
	 *
	 * @param string $dir
	 *        	文件夹,再次调用该函数传递的该参数将被忽略。
	 *        	
	 * @return \icms\Evaluation\Sequence
	 */
	static public function Instance ($dir = null) {
		static $me = null;
		if (null == $me) {
			if (null == $dir) {
				throw new ParameterErrorException('instance' . __CLASS__ . ' need dir parameter.');
			}
			
			$me = new Sequence();
			$me->mSaveDir = $dir;
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
		$fpath = $this->mSaveDir . $triggerName;
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
		
		return (int) $next;
	}
}
?>