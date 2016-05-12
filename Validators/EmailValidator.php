<?php
/**
 * Created by PhpStorm.
 * User: HeYanLong
 * Date: 2015/12/3
 * Time: 17:29
 */

namespace Framework\Validators;

use Framework\Validators\Exception\ValidatorException;

class EmailValidator extends ValidatorService {

	/**
	 * @var 正常邮件验证正则
	 * @see http://www.regular-expressions.info/email.html
	 */
	public $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
	/**
	 * @var 带名字的电子邮件检查正则
	 * [[allowName]] 设置为true的时候会使用
	 * @see allowName
	 */
	public $fullPattern = '/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';
	/**
	 * @var boolean 是否允许带名字的邮件 (如 "heyanlong <heyanlong@che001.com>"). 默认关闭
	 * @see fullPattern
	 */
	public $allowName = false;
	/**
	 * @var boolean 检查电子邮件域名是否存在，包含 A 或者 MX 标记.
	 * 在DNS服务器宕机时会造成检查失败，慎用
	 */
	public $checkDNS = false;
	/**
	 * @var boolean 是否启用IDN(国际化域名)账户验证.
	 * 如果启用，请安装`intl` PHP 扩展,
	 * 否则会抛出异常
	 */
	public $enableIDN = false;

	/**
	 * 字符编码
	 * @var string
	 */
	public $charset = 'UTF-8';

	protected function validateValue($value) {
		if (!is_string($value)) {
			$valid = false;
		} elseif (!preg_match('/^(?P<name>(?:"?([^"]*)"?\s)?)(?:\s+)?(?:(?P<open><?)((?P<local>.+)@(?P<domain>[^>]+))(?P<close>>?))$/i', $value, $matches)) {
			$valid = false;
		} else {
			if ($this->enableIDN) {
				$matches['local'] = idn_to_ascii($matches['local']);
				$matches['domain'] = idn_to_ascii($matches['domain']);
				$value = $matches['name'] . $matches['open'] . $matches['local'] . '@' . $matches['domain'] . $matches['close'];
			}
			if (strlen($matches['local']) > 64 || mb_strlen($matches['name'], $this->charset) > 64) {
				// The maximum total length of a user name or other local-part is 64 octets. RFC 5322 section 4.5.3.1.1
				// http://tools.ietf.org/html/rfc5321#section-4.5.3.1.1
				$valid = false;
			} elseif (strlen($matches['local'] . '@' . $matches['domain']) > 254) {
				// There is a restriction in RFC 2821 on the length of an address in MAIL and RCPT commands
				// of 254 characters. Since addresses that do not fit in those fields are not normally useful, the
				// upper limit on address lengths should normally be considered to be 254.
				//
				// Dominic Sayers, RFC 3696 erratum 1690
				// http://www.rfc-editor.org/errata_search.php?eid=1690
				$valid = false;
			} else {
				$valid = preg_match($this->pattern, $value) || $this->allowName && preg_match($this->fullPattern, $value);
				if ($valid && $this->checkDNS) {
					$valid = checkdnsrr($matches['domain'], 'MX') || checkdnsrr($matches['domain'], 'A');
				}
			}
		}

		if ($valid) {
			return null;
		}

		throw new ValidatorException($this->errorMessage, $this->errorCode);
	}
}