<?php
return array(
	'boot_module' => 'app',
	
	'default_controller' => array(
		'controller' => 'TestController',
		'action' => 'index'
	),
	'module' => array(
		'app' => array(
			'name' => 'app',
			'enable' => true,
			'config' => 'test' . DIRECTORY_SEPARATOR . 'TestSub' . DIRECTORY_SEPARATOR,
			'dependence' => array()
		)
	),
	//各模块的绝对路径，主要是autoload时用到，为了加快索引速度。
	'module_dir_index' => array(
		\Hhp\App::getRootDir() . 'test' . DIRECTORY_SEPARATOR . 'TestSub' . DIRECTORY_SEPARATOR => 'TestSub'
	)
);
?>