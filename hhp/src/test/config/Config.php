<?php
return array(
	'version' => '3.34.5',
	'controller_dir' => 'controller' . DIRECTORY_SEPARATOR,
	
	'default_controller' => array(
		'controller_name' => 'Test',
		'action_name' => 'index'
	),
	
	'module' => array(
		'TestSub' => array(
			'name' => 'TestSub',
			'enable' => true,
			'dir' => 'test/TestSub/'
		),
		'TestSub1' => array(
			'name' => 'TestSub1',
			'enable' => false,
			'dir' => 'test/TestSub1/'
		),
		'TestSub2' => array(
			'name' => 'TestSub2',
			'enable' => true,
			'dir' => 'test/TestSub2/'
		)
	),
	
	/**
	 * 本模块提供的API，以类的形式给出。数组中的key代表类名，值是另外一个数组，其中enbale表示是否允许这个接口开放，这样方便以后对接口进行更加详细的控制。
	 */
	'API' => array(),
	
	/**
	 * 本模块提供的Controller
	 */
	'controller' => array(
		'test\controller\TestController' => array(
			'enable' => true
		),
		'test\controller\RunTestController' => array(
			'enable' => true
		),
		'test\controller\TriggerTestController' => array(
			'enable' => true
		)
	),
	
	/**
	 * 本模块依赖的其他模块，用数组的key给出名字，其值暂时保留以后扩展。
	 */
	'depends' => array(
		'TestSub' => array()
	)
);
?>