<?php

namespace Framework\Facade;

use Framework\Module\ModuleManager;

class Module {

	static public function getService ($moduleAlias, $api) {
		return ModuleManager::Instance()->getService($moduleAlias, $api);
	}
}