<?php

namespace Framework\Output;

/**
 * php默认的输出，及用echo可以打印出的输出
 *
 * @author Hoheart
 *        
 */
class StandardOutputStream implements IOutputStream {

	public function write ($str, $offset = 0, $count = -1) {
		$subStr = substr($str, $offset, $count);
		echo $subStr;
	}

	public function flush () {
		ob_flush();
		flush();
	}

	public function close () {
	}
}