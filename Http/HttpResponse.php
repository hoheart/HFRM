<?php

namespace Framework\Http;

use Framework\IHttpResponse;
use Framework\HFC\Exception\ParameterErrorException;

/**
 * 目前支持http1.0，不支持chunked
 *
 * @author Hoheart
 *        
 */
class HttpResponse extends HttpMessage implements IHttpResponse {
	
	/**
	 *
	 * @var int $mStatusCode
	 */
	protected $mStatusCode = 200;
	
	/**
	 *
	 * @var string $mReasonPhrase
	 */
	protected $mReasonPhrase = 'OK';

	/**
	 *
	 * @param string $respStr        	
	 * @param int $endPos
	 *        	两个回车的位置
	 */
	public function __construct ($respStr = '') {
		$this->continueParse($respStr);
	}

	public function continueParse ($str) {
		if (! is_string($str) || '' === $str) {
			throw new ParameterErrorException();
		}
		
		if (0 == $bodyPos) {
			$bodyPos = strpos($respStr, "\r\n\r\n");
		}
		$pos = $this->parseCommandLine($respStr);
		$this->parseHeaderMap($respStr, $pos, $bodyPos);
		$this->mBody = substr($respStr, $bodyPos + 4);
	}

	protected function parseCommandLine ($str) {
		$pos = strpos($str, "\r\n");
		if (false === $pos) {
			throw new ParameterErrorException();
		}
		
		$cmdLine = substr($str, 0, $pos);
		list ($version, $code, $phrase) = explode(' ', $cmdLine);
		$this->mVersion = $version;
		$this->mStatusCode = $code;
		$this->mReasonPhrase = $phrase;
		
		return $pos + 2;
	}

	protected function parseHeaderMap ($str, $pos, $bodyPos) {
		$strLen = strlen($str);
		$endPos = 0;
		do {
			$endPos = strpos($str, "\r\n", $pos);
			if (false === $endPos) {
				$endPos = $strLen;
			}
			
			$line = substr($str, $pos, $endPos - $pos);
			list ($key, $val) = explode(':', $line);
			$this->mHeader[$key] = ltrim($val);
			
			$pos = $endPos + 2;
		} while ($endPos < $bodyPos);
	}

	public function setStatusCode ($code) {
		$this->mStatusCode = $code;
	}

	public function setReasonPhrase ($reason) {
		$this->mReasonPhrase = $reason;
	}

	public function addBody ($str) {
		$this->mBody .= $str;
	}
}