<?php
/**
 * 注意，凡是路径的配置，都用DIRECTORY_SEPARATOR作为文件夹分隔符，如果是目录，末尾必须有一个文件夹分隔符。
 */
return array(
	// 应用程序的版本，非框架的版本。
	'version' => '1.01',
	'boot_module' => 'Test',
	'default_controller' => array(
		'controller' => 'TestController',
		'action' => 'index'
	),
	'route' => array(
		'pre_executer' => array(),
		'later_executer' => array()
	),
	'module' => array(
		'icms' => array(
			'name' => 'icms',
			'dir' => 'icms' . DIRECTORY_SEPARATOR,
			'controller_dir' => 'Controller' . DIRECTORY_SEPARATOR,
			'enable' => true,
			'dependence' => array()
		),
		'test' => array(
			'name' => 'test',
			'dir' => 'test' . DIRECTORY_SEPARATOR,
			'controller_dir' => 'Controller' . DIRECTORY_SEPARATOR,
			'enable' => true,
			'dependence' => array(
				'TestSub'
			)
		)
	),
	/**
	 * 用绝对路径作索引的模块名称。为了加快查找和载入速度。
	 */
	'module_dir_index' => array(
		dirname(__DIR__) . DIRECTORY_SEPARATOR . 'icms' . DIRECTORY_SEPARATOR => 'icms',
		dirname(__DIR__) . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR => 'test'
	),
	'service' => array(
		'db' => array(
			'class' => 'Hfc\Database\DatabaseClientFactory',
			'method' => 'create',
			'config' => array(
				'dbms' => 'mysql',
				'user' => 'root',
				'password' => '',
				'server' => '127.0.0.1',
				'port' => 3306,
				'name' => 'test123',
				'charset' => 'utf8'
			)
		),
		'event' => array(
			'class' => 'Hfc\Event\EventManager',
			'config' => array(
				'Hfc\Event\CommonEvent' => array()
			)
		),
		'log' => array(
			'class' => 'Hfc\Util\Logger',
			'config' => array(
				// 由于日志文件很可能与其他数据文件，所以一般单独指定文件夹。
				'root_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR,
				// 缓存大小，单位byte
				'buffer_size' => 50000,
				// 写入文件事件间隔
				'interval' => 30,
				// 是否记录调试日志。
				'debug_log' => false,
				// 每个日志文件的大小，单位m
				'file_size' => 50,
				// 是否启用日志记录
				'enable' => true
			)
		)
	),
	/**
	 * 整个系统存放数据的目录，包括日志的存放。所以，不需要对日志进行单独的目录配置。
	 * 通过调用系统提供的日志
	 */
	'data_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data'
);
