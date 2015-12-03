<?php
/**
 * Created by PhpStorm.
 * User: HeYanLong
 * Date: 2015/12/3
 * Time: 16:14
 */

namespace Framework\Validators;


use Framework\Validators\Exception\ValidatorException;

class RequiredValidator extends ValidatorService {

	/**
	 * 所验证的值必须包含此值
	 * @var
	 */
	public $requiredValue;

	/**
	 * 如果为true 则校验的值必须和requiredValue类型一致
	 * 如果为false 则只需匹配即可
	 * @var bool
	 */
	public $strict = false;

	protected function validateValue($value) {
		if ($this->requiredValue === null) {
			if ($this->strict && $value !== null || !$this->strict && !$this->isEmpty(is_string($value) ? trim($value) : $value)) {
				return null;
			}
		} elseif (!$this->strict && $value == $this->requiredValue || $this->strict && $value === $this->requiredValue) {
			return null;
		}
		if ($this->requiredValue === null) {
			throw new ValidatorException($this->attributes . ' can not be empty');
		} else {
			throw new ValidatorException($this->attributes . ' must be ' . $this->requiredValue);
		}
	}
}