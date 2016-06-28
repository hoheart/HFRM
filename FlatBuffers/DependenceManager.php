<?php

namespace Framework;

class DependenceManager {
	
	/**
	 * 接口名对应的接口类名
	 *
	 * @var array
	 */
	protected $mApiName2Interface = array();

	public function downloadAllFbs () {
		$moduleArr = Config::Instance()->get('module');
		foreach ($moduleArr as $moduleAlias => $conf) {
			$url = $conf['path'] . '/fbs';
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$resp = curl_exec($ch);
			
			while (true) {
				$pos1 = 0;
				$pos2 = 0;
				$pos2 = strpos($resp, ':');
				if (false === $pos2) {
					break;
				}
				
				$apiName = substr($resp, $pos1, $pos2 - $pos1);
				$pos1 = $pos2 + 1;
				
				$pos2 = strpos($resp, '|', $pos1);
				$fileContent = substr($resp, $pos1, $pos2 - $pos1);
				
				$dataDir = Config::Instance()->get('app.dataDir');
				file_put_contents($dataDir . $apiName, $fileContent);
			}
		}
	}
}