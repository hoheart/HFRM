<?php

namespace test;

abstract class AbstractTest {
	
	/**
	 * test函数，由框架调用。
	 */
	abstract public function test();
	
	/**
	 * 抛出错误，主要是针对测试类里的方法名。
	 *
	 * @param string $msg        	
	 * @param string $methodName        	
	 * @param string $lineno        	
	 */
	public function throwError($msg, $methodName, $lineno) {
		$msg = "Error:$msg<br>Method:$methodName<br>Line:$lineno";
		
		throw new \Exception ( $msg );
	}
	
	public function getErrorMethod(){
		return $this->mErrorMethodName;
	}
	
	public function getErrorLineNumber(){
		return $this->mErrorLineNumber;
	}
}

?>