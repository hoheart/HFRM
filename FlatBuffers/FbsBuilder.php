<?php

namespace Framework\FlatBuffers;

use Framework\IService;

class FbsBuilder implements IService {

	public function init (array $conf) {
	}

	public function start () {
	}

	public function stop ($normal = true) {
	}

	public function createFbs ($apiName) {
		$filePath = __DIR__ . DIRECTORY_SEPARATOR . $apiName . '.fbs';
		if (file_exists($filePath)) {
			return;
		}
		
		$fp = fopen($filePath, 'a+');
		$ref = new \ReflectionClass($apiName);
		$methodMap = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
		foreach ($methodMap as $methodName => $refMethod) {
			$doc = $refMethod->getDocDocument();
			$content = $this->createOneMethodFbs($doc);
			$content .= "\n";
			
			fwrite($fp, $content);
		}
		fclose($fp);
	}

	protected function createOneMethodFbs ($doc) {
		$arr = $this->parseDocComment($doc);
	}

	protected function parseDocComment ($doc) {
		if ('' == $doc) {
			return;
		}
		$doc = ltrim(substr($doc, 3, strlen($doc) - 5), ' \t'); // ReflectionClass只支持/**格式的注释
		
		$desc = '';
		$docEnded = false;
		$keyVal = array();
		$var = '';
		$arr = preg_split('/@|(\r?\n[ \t\*]*)/', $doc, - 1, PREG_SPLIT_OFFSET_CAPTURE);
		foreach ($arr as $item) {
			list ($str, $pos) = $item;
			if ('@' == $doc[$pos - 1]) {
				$docEnded = true;
				
				$rowArr = preg_split('/[ \t]/', $str);
				if ('orm' == $rowArr[0]) {
					$keyVal[$rowArr[1]] = $rowArr[2];
				} else if ('var' == $rowArr[0]) {
					$var = $rowArr[1];
				}
			} else if (! $docEnded) {
				$desc .= $str;
			}
		}
		
		if (! key_exists('var', $keyVal)) {
			$keyVal['var'] = $var;
		}
		if (! key_exists('desc', $keyVal)) {
			$keyVal['desc'] = $desc;
		}
		
		return $keyVal;
	}
}