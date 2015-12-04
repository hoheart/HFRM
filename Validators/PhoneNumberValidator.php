<?php
/**
 * Created by PhpStorm.
 * User: HeYanLong
 * Date: 2015/12/4
 * Time: 14:24
 */

namespace Framework\Validators;

use Framework\Validators\Exception\ValidatorException;

/**
 * 手机号验证，仅支持国内手机格式
 * Class PhoneNumberValidator
 * @package Framework\Validators
 */
class PhoneNumberValidator extends ValidatorService {

	/**
	 * 是否批量检查 如 18888888888,18388888888
	 * @var bool
	 */
	public $multi = false;

	/**
	 * 多个手机号分隔符
	 * @var string
	 */
	public $splitter = ',';

	/**
	 * @var string 验证整数正则表达式
	 */
	public $phonePattern = '/^(13[0-9]|15[012356789]|17[0678]|18[0-9]|14[57])[0-9]{8}$/';

	/**
	 * 多个手机号同时验证
	 * @var string 验证整数正则表达式
	 */
	public $multiPhonePattern = '/^(13[0-9]|15[012356789]|17[0678]|18[0-9]|14[57])[0-9]{8}(?:\,(13[0-9]|15[012356789]|17[0678]|18[0-9]|14[57])[0-9]{8})*$/';

	/**
	 * @inheritdoc
	 */
	protected function validateValue($value) {

		if (is_array($value)) {
			throw new ValidatorException($this->attributes . " is invalid.");
		}

		$multiPhonePattern = $this->multi ? str_replace(',', $this->splitter, $this->multiPhonePattern) : "";

		$pattern = $this->multi ? $multiPhonePattern : $this->phonePattern;

		if (!preg_match($pattern, "$value")) {
			throw new ValidatorException($this->errorMessage, $this->errorCode);
		} else {
			return null;
		}
	}
}