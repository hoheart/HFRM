<?php

namespace Framework\Facade;

use Framework\Module\ModuleManager;

class Module {

	static public function getService ($moduleAlias, $api) {
		$m = ModuleManager::Instance()->get($moduleAlias);
		return $m->getService($api);
	}
}