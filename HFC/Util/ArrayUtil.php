<?php

namespace Framework\HFC\Util;

/**
 * 数组相关的操作
 *
 * @author Hoheart
 *        
 */
class ArrayUtil {

	/**
	 * 递归合并两个数组。与系统提供的array_merge函数不同的是，如果遇到键相同，后面的数组覆盖前面的，而不是追加。
	 * 本函数相当于两个数组用加号(+)运算符，只不过该函数是递归的。
	 *
	 * 自php5.3起，可以用array_replace_recursive代替。
	 *
	 * @param array $a        	
	 * @param array $b        	
	 */
	static public function MergeArray (array $a = null, array $b = null) {
		if (null == $a) {
			return $b;
		}
		if (null == $b) {
			return $a;
		}
		
		$ret = $a;
		foreach ($b as $key => $val) {
			// 如果值是数组，但就是普通数组，即键不是字母，就替换
			if (is_array($val) && ! array_key_exists(0, $val) && ! empty($val)) {
				$ret[$key] = self::MergeArray($a[$key], $val);
			} else {
				$ret[$key] = $val;
			}
		}
		
		return $ret;
	}

	/**
	 * 把php的关联数组转成php代码。
	 *
	 * @param array $arr        	
	 * @return string
	 */
	static public function MapToCode ($arr) {
		if (! is_array($arr)) {
			$strVal = $arr;
			if (is_string($strVal)) {
				$strVal = str_replace('\\', '\\\\', $strVal);
				$strVal = str_replace('\'', '\\\'', $strVal);
				$strVal = "'$strVal'";
			} else if (is_bool($strVal)) {
				$strVal = $strVal ? 'true' : 'false';
			} else if (null === $strVal) {
				$strVal = 'null';
			}
			return $strVal;
		}
		
		$code = '';
		foreach ($arr as $key => $val) {
			$strKey = self::MapToCode($key);
			
			$strVal = self::MapToCode($val);
			
			if (! empty($code)) {
				$code .= ',';
			}
			
			$code .= "$strKey=>$strVal";
		}
		
		$code = "array($code)";
		
		return $code;
	}

	/**
	 * 对于像配置文件那种层次很深的数组，直接用点号取得对应路径的值
	 *
	 * @param string $path        	
	 */
	static public function GetValueByPath ($arr, $path) {
		if (empty($arr)) {
			return null;
		}
		if (null === $path || '' === $path || false === $path) {
			return $arr;
		}
		
		$arrOfPath = explode('.', $path);
		
		$curnt = $arr;
		foreach ($arrOfPath as $key) {
			if (array_key_exists($key, $curnt)) {
				$curnt = $curnt[$key];
				if (null == $curnt) {
					break;
				}
			} else {
				return null;
			}
		}
		
		return $curnt;
	}
}