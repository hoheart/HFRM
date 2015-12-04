<?php
/**
 * Created by PhpStorm.
 * User: HeYanLong
 * Date: 2015/12/4
 * Time: 9:49
 */

namespace Framework\Validators;

use Framework\Validators\Exception\ValidatorException;

class NumberValidator extends ValidatorService {
	/**
	 * @var boolean 是否只能是整数，默认false
	 */
	public $integerOnly = false;
	/**
	 * @var integer|float 要验证数值的最大值，默认为无限大
	 */
	public $max;
	/**
	 * @var integer|float 要验证数值的最小值，默认为无限小
	 */
	public $min;
	/**
	 * @var string 验证整数正则表达式
	 */
	public $integerPattern = '/^\s*[+-]?\d+\s*$/';
	/**
	 * @var string 默认验证规则
	 * 支持科学计数法 (e.g. -1.23e-10).
	 */
	public $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';

	/**
	 * @inheritdoc
	 */
	protected function validateValue($value) {
		if (is_array($value)) {
			throw new ValidatorException($this->attributes . " is invalid.");
		}
		$pattern = $this->integerOnly ? $this->integerPattern : $this->numberPattern;
		if (!preg_match($pattern, "$value")) {
			throw new ValidatorException($this->errorMessage, $this->errorCode);
		} elseif ($this->min !== null && $value < $this->min) {
			throw new ValidatorException($this->attributes . " is too small.", $this->errorCode);
		} elseif ($this->max !== null && $value > $this->max) {
			throw new ValidatorException($this->attributes . " is too big.", $this->errorCode);
		} else {
			return null;
		}
	}
}