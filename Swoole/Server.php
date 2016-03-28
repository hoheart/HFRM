<?php

namespace Framework\Swoole;

use Framework\Config;
use Framework\Output\SwooleHttpOutputStream;
use Framework\Swoole\HttpRequest;
use Framework\App;

class Server {
	
	/**
	 * swoole服务器
	 *
	 * @var swoole_http_server
	 */
	protected $mServer;
	
	/**
	 *
	 * @var Framework\App
	 */
	protected $mApp;
	
	/**
	 *
	 * @var IOutputStream $mOutputStream
	 */
	protected $mOutputStream = null;
	protected $mGlobal = null;
	
	/**
	 *
	 * @var string
	 */
	protected $mServerName = 'mdserver';
	
	/**
	 * pid文件路径
	 *
	 * @var string
	 */
	public static $PID_FILE_PATH = '';

	public function __construct ($pidFilePath) {
		self::$PID_FILE_PATH = $pidFilePath;
		$this->mApp = App::Instance();
	}

	public function init () {
		$this->mApp->init();
		
		$config = Config::Instance();
		
		$this->mServerName = $config->get('server.serverName');
		\swoole_set_process_name($this->mServerName);
		
		$sm = $this->mApp->getServiceManager();
		$sm->initPoolService('db');
		
		$this->mOutputStream = new SwooleHttpOutputStream();
		
		$host = $config->get('server.host');
		$port = $config->get('server.port');
		
		$this->mServer = new \swoole_http_server($host, $port);
		$swooleConfig = $config->get('server.swooleConfig');
		$this->mServer->set($swooleConfig);
		
		$this->mServer->on('request', array(
			$this,
			'onRequest'
		));
		$this->mServer->on('start', array(
			$this,
			'onStart'
		));
		$this->mServer->on('workerStart', 
				function  () {
					\swoole_set_process_name($this->mServerName . '_worker');
				});
		
		$this->mServer->on('managerStart', 
				function  () {
					\swoole_set_process_name($this->mServerName . '_manager');
				});
	}

	public function start () {
		$this->mServer->start();
	}

	public function onStart () {
		$pid = $this->mServer->master_pid;
		$fp = fopen(self::$PID_FILE_PATH, 'a+');
		ftruncate($fp, 0);
		fwrite($fp, $pid);
		fclose($fp);
	}

	public function onRequest ($req, $resp) {
		$this->mOutputStream->setSwooleResponse($resp);
		$this->mApp->setOutputStream($this->mOutputStream);
		
		$this->mApp->run(new HttpRequest($req));
	}

	public function stop () {
		if (file_exists(self::$PID_FILE_PATH)) {
			$pid = file_get_contents(self::$PID_FILE_PATH);
			@\swoole_process::kill($pid);
			unlink(self::$PID_FILE_PATH);
		}
	}

	public function main () {
		global $argv;
		$op = '';
		if (isset($argv[1])) {
			$op = trim($argv[1]);
		}
		$op = strtolower($op);
		
		switch ($op) {
			case 'stop':
				$this->stop();
				break;
			case 'restart':
				$this->stop();
			
			// break;
			default:
				$this->init();
				$this->start();
				break;
		}
		
		$this->stop();
	}
}