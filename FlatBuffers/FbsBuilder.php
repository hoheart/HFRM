<?php

namespace Framework\FlatBuffers;

use Framework\IService;
use Framework\Config;
use Framework\Exception\NotImplementedException;

class FbsBuilder implements IService {

	public function init (array $conf = array()) {
	}

	public function start () {
	}

	public function stop ($normal = true) {
	}

	/**
	 *
	 * @return \Framework\FlatBuffers\FbsBuilder
	 */
	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$cls = get_called_class();
			$me = new $cls();
		}
		
		return $me;
	}

	public function getFbs ($apiName) {
		$filePath = Config::Instance()->get('app.dataDir') . $apiName . '.fbs';
		if (! file_exists($filePath)) {
			$this->createFbsFile($apiName);
		}
		
		$content = file_get_contents($filePath);
		
		return $content;
	}

	protected function createFbsFile ($apiName) {
		$filePath = Config::Instance()->get('app.dataDir') . $apiName . '.fbs';
		if (file_exists($filePath)) {
			return;
		}
		
		$fp = fopen($filePath, 'a+');
		try {
			$clsName = Config::Instance()->get('moduleService.' . $apiName);
			$arr = class_implements($clsName);
			if (empty($arr)) {
				throw new NotImplementedException();
			}
			$apiClsName = current($arr);
			
			// 写名字空间
			$nameSpace = str_replace('\\', '.', $apiClsName);
			fwrite($fp, "namespace $nameSpace;\r\n\r\n");
			
			$ref = new \ReflectionClass($apiClsName);
			$methodMap = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
			foreach ($methodMap as $refMethod) {
				if ($refMethod->class != $apiClsName) {
					continue;
				}
				$methodName = $refMethod->name;
				$doc = $refMethod->getDocComment();
				$content = $this->createOneMethodFbs($doc, $methodName);
				
				fwrite($fp, $content);
			}
		} catch (\Exception $e) {
			fclose($fp);
			unlink($filePath);
			
			throw $e;
		}
		fclose($fp);
	}

	protected function createOneMethodFbs ($doc, $methodName) {
		$content = '';
		
		$arr = $this->parseDocComment($doc);
		if (! empty($arr['param'])) {
			$content = 'table ' . $methodName . "_req {";
			$content .= "\r\n\tparams:[string];";
			foreach ($arr['param'] as $paramName => $paramType) {
				$content .= "\r\n\t" . $paramName . ':' . $paramType . ';';
			}
			$content .= "\r\n}\r\n\r\n";
		}
		
		return $content;
	}

	protected function parseDocComment ($doc) {
		if ('' == $doc) {
			return;
		}
		$doc = ltrim(substr($doc, 3, strlen($doc) - 5), ' \t'); // ReflectionClass只支持/**格式的注释
		
		$keyVal = array();
		// 切分每一个@，有可能一行里写两个@
		$arr = preg_split('/@|(\r?\n[ \t\*]*)/', $doc, - 1, PREG_SPLIT_OFFSET_CAPTURE);
		foreach ($arr as $item) {
			list ($str, $pos) = $item;
			if ('@' == $doc[$pos - 1]) {
				$rowArr = preg_split('/[ \t]/', $str);
				if ('param' == $rowArr[0]) {
					$paramName = $rowArr[2];
					if ('$' == $paramName[0]) {
						$paramName = substr($paramName, 1);
					}
					$keyVal['param'][$paramName] = $rowArr[1];
				} else if ('return' == $rowArr[0]) {
					$keyVal['return'] = $rowArr[1];
				}
			}
		}
		
		return $keyVal;
	}
}