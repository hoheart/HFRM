<?php

namespace Hfc\Util;

/**
 * 存放没有归为一个类的函数。
 *
 * @author Hoheart
 *        
 */
class Util {

	/**
	 * 递归合并两个数组。与系统提供的array_merge函数不同的是，如果遇到键相同，后面的数组覆盖前面的，而不是追加。
	 * 本函数相当于两个数组用加号(+)运算符，只不过该函数是递归的。
	 *
	 * @param array $a        	
	 * @param array $b        	
	 */
	static public function mergeArray (array $a = null, array $b = null) {
		if (null == $a) {
			return $b;
		}
		if (null == $b) {
			return $a;
		}
		
		$ret = $a;
		foreach ($b as $key => $val) {
			if (is_array($val)) {
				$ret[$key] = self::mergeArray($a[$key], $val);
			} else {
				$ret[$key] = $val;
			}
		}
		
		return $ret;
	}
}
?>