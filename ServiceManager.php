<?php

namespace hhp;

class ServiceManager {
	protected $mServiceMap = null;
	protected $mConfig = null;

	public function __construct (array $conf) {
		$this->mConfig = $conf;
	}

	public function getService ($name) {
		$s = $this->mServiceMap[$name];
		if (null == $s) {
			$conf = $this->mConfig[$name];
			$clsName = $conf['class'];
			$method = $conf['method'];
			$serviceConf = $conf['config'];
			$s = null;
			if (! empty($method)) {
				$factory = new $clsName();
				$s = $factory->$method($serviceConf);
			} else {
				$s = new $clsName($serviceConf);
			}
			
			$this->mServiceMap[$name] = $s;
		}
		
		return $s;
	}
}
?>