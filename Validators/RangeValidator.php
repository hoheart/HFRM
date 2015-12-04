<?php
/**
 * Created by PhpStorm.
 * User: HeYanLong
 * Date: 2015/12/4
 * Time: 10:38
 */

namespace Framework\Validators;


use Framework\Validators\Exception\ValidatorException;

class RangeValidator extends ValidatorService {
	/**
	 * @var array|\Traversable|\Closure 要验证的规则列表
	 */
	public $range;
	/**
	 * @var boolean 是否严格查找，类型必须一致
	 */
	public $strict = false;
	/**
	 * @var boolean 是否翻转验证逻辑，如果设置为true，则验证的值不包含在range中是返回true
	 */
	public $not = false;
	/**
	 * @var boolean 是否验证数组的属性
	 */
	public $allowArray = false;

	/**
	 * @inheritdoc
	 */
	protected function validateValue($value) {
		$in = false;
		if ($this->allowArray
			&& ($value instanceof \Traversable || is_array($value))
			&& $this->isSubset($value, $this->range, $this->strict)
		) {
			$in = true;
		}
		if (!$in && $this->isIn($value, $this->range, $this->strict)) {
			$in = true;
		}

		if ($this->not !== $in) {
			return null;
		}

		throw new ValidatorException($this->errorMessage, $this->errorCode);
	}

	/**
	 * 检查数组或[[\Traversable]] 是否包含一个元素
	 *
	 * 和 in_array 一样使用 [in_array()](http://php.net/manual/en/function.in-array.php)
	 * 但是不值实现了array 还支持 [[\Traversable]] interface 对象
	 * @param mixed $needle 要查找的值
	 * @param array|\Traversable $haystack 被搜索的数组或接口
	 * @param boolean $strict 是否启用严格查找 (`===`).
	 * @return boolean 是否查找到
	 * @throws ValidatorException 输入参数错误
	 * @see http://php.net/manual/en/function.in-array.php
	 */
	private function isIn($needle, $haystack, $strict = false) {
		if ($haystack instanceof \Traversable) {
			foreach ($haystack as $value) {
				if ($needle == $value && (!$strict || $needle === $haystack)) {
					return true;
				}
			}
		} elseif (is_array($haystack)) {
			return in_array($needle, $haystack, $strict);
		} else {
			throw new ValidatorException('params error1');
		}
		return false;
	}

	/**
	 * 检查数组或[[\Traversable]] 是否是另一个数组或[[\Traversable]]的子集
	 *
	 * 如果全部存在则返回true，如果有一个元素不存在则返回false
	 * @param array|\Traversable 要查找的值
	 * @param array|\Traversable $haystack 被搜索的数组或接口
	 * @param boolean $strict 是否启用严格查找 (`===`).
	 * @throws ValidatorException 输入参数错误
	 * @return boolean 是否查找到
	 */
	private function isSubset($needles, $haystack, $strict = false) {
		if (is_array($needles) || $needles instanceof \Traversable) {
			foreach ($needles as $needle) {
				if (!static::isIn($needle, $haystack, $strict)) {
					return false;
				}
			}
			return true;
		} else {
			throw new ValidatorException('params error');
		}
	}
}