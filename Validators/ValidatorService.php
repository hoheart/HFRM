<?php

/**
 * Created by PhpStorm.
 * User: HeYanLong
 * Date: 2015/12/3
 * Time: 15:35
 */
namespace Framework\Validators;

use Framework\IService;
use Framework\Validators\Exception\ValidatorErrorCode;
use Framework\Validators\Exception\ValidatorException;

class ValidatorService implements IService {

	protected $errorCode = ValidatorErrorCode::ValidatorError;

	protected $errorMessage = 'Validator Error';

	protected $attributes;

	private $rules;

	public static $builtInValidators = [
		'required' => 'Framework\Validators\RequiredValidator',
		'email' => 'Framework\Validators\EmailValidator',
		'number' => 'Framework\Validators\NumberValidator',
		'in' => 'Framework\Validators\RangeValidator',
		'phone' => 'Framework\Validators\PhoneNumberValidator',
	];


	public function setRules(array $rules) {

		// 检测规则是否合法
		if (!empty($rules)) {
			foreach ($rules as $key => $rule) {
				if (!empty($rule[0]) && !empty($rule[1]) && is_string($rule[1])) {
					if (!is_array($rules[0]) && !is_string($rule[0])) {
						throw new ValidatorException('rule error');
					}

					if (!isset(self::$builtInValidators[$rule[1]])) {
						throw new ValidatorException('rule error');
					}

				} else {
					throw new ValidatorException('rule error');
				}
			}
		} else {
			throw new ValidatorException('rule error');
		}

		$this->rules = $rules;
	}

	public function validate($data) {
		foreach ($this->rules as $rule) {

			$attributes = $rule[0];
			if (is_string($attributes)) {
				$attributes = [$attributes];
			}

			$validateAliases = $rule[1];

			array_shift($rule);
			array_shift($rule);

			$validateParams = $rule;

			foreach ($attributes as $attr) {
				$this->attributes = $attr;

				$validateObj = $this->_createObject($validateAliases, $validateParams);

				if (isset($data[$this->attributes])) {
					$validateObj->validateValue($data[$this->attributes]);
				} else {
					throw new ValidatorException('the ' . $this->attributes . ' attribute not set.');
				}
			}

		}
	}

	protected function validateValue($value) {
		throw new ValidatorException;
	}

	public function isEmpty($value) {
		return $value === null || $value === [] || $value === '';
	}

	private function _createObject($aliases, $validateParams = []) {

		if (isset(self::$builtInValidators[$aliases])) {
			$className = self::$builtInValidators[$aliases];
			$obj = new $className;
			$obj->attributes = $this->attributes;
			$obj->errorCode = $this->errorCode;
			$obj->errorMessage = $this->errorMessage;

			if (!empty($validateParams)) {
				foreach ($validateParams as $attr => $value) {
					$obj->$attr = $value;
				}
			}

			return $obj;
		}

	}

	/**
	 * 对服务进行初始化
	 *
	 * @param array $conf
	 */
	public function init(array $conf) {

	}

	/**
	 * 启动服务
	 */
	public function start() {

	}

	/**
	 * 停止服务，回收资源
	 */
	public function stop() {

	}
}