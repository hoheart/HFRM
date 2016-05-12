<?php

namespace Framework\Output;

use Framework\Response\IResponse;

interface IOutputStream {

	/**
	 * 输出
	 *
	 * @param string $str        	
	 * @param int $offset        	
	 * @param int $count        	
	 */
	public function write ($str, $offset = 0, $count = -1);

	/**
	 * 把缓冲区的内容输出
	 */
	public function flush ();

	/**
	 * 只是表示输出流断掉了，链接并不一定断
	 */
	public function close ();

	/**
	 * 直接输出一个对象
	 *
	 * @param IResponse $resp        	
	 */
	public function output (IResponse $resp);
}