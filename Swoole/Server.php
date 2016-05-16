<?php

namespace Framework\Swoole;

use Framework\Output\SwooleHttpOutputStream;
use Framework\Swoole\HttpRequest;
use Framework\App;
use Framework\Module\ModuleManager;
use Framework\Facade\Config;

class Server {
	
	/**
	 * 默认的连接数
	 *
	 * @var int
	 */
	const DEFAULT_CONNECTIONS_NUM = 5;
	
	/**
	 *
	 * @var int
	 */
	const ERRCODE_DB_RECONNECT = 1;
	
	/**
	 * pid文件路径
	 *
	 * @var string
	 */
	public static $PID_FILE_PATH = '';
	
	/**
	 * swoole服务器
	 *
	 * @var swoole_http_server
	 */
	protected $mServer;
	
	/**
	 *
	 * @var \Framework\App
	 */
	protected $mApp;
	
	/**
	 *
	 * @var IOutputStream $mOutputStream
	 */
	protected $mOutputStream = null;
	
	/**
	 *
	 * @var string
	 */
	protected $mServerName = 'mdserver';
	
	/**
	 *
	 * @var int $mNeedExitErrorCode
	 */
	protected $mNeedExitErrorCode = 0;
	
	/**
	 * 对象池集合
	 *
	 * @var array $mPoolArr
	 */
	protected $mPoolArr = array();

	protected function __construct () {
		$this->mApp = App::Instance();
		
		self::$PID_FILE_PATH = dirname(Config::get('server.swooleConfig.log_file')) . DIRECTORY_SEPARATOR . '.pid';
	}

	static public function Instance () {
		static $me = null;
		if (null === $me) {
			$me = new self();
		}
		
		return $me;
	}

	protected function init () {
		$this->mApp->init();
		
		$this->mServerName = Config::get('server.serverName');
		\swoole_set_process_name($this->mServerName);
		
		$this->initPoolService();
		
		$this->mOutputStream = new SwooleHttpOutputStream();
		
		$host = Config::get('server.host');
		$port = Config::get('server.port');
		
		$this->mServer = new \swoole_http_server($host, $port);
		$swooleConfig = Config::get('server.swooleConfig');
		$this->mServer->set($swooleConfig);
		
		$this->mServer->on('request', array(
			$this,
			'onRequest'
		));
		$this->mServer->on('start', array(
			$this,
			'onStart'
		));
		$this->mServer->on('shutdown', 
				function  () {
					if (file_exists(self::$PID_FILE_PATH)) {
						@unlink(self::$PID_FILE_PATH);
					}
				});
		$this->mServer->on('workerStart', 
				function  () {
					\swoole_set_process_name($this->mServerName . '_worker');
				});
		
		$this->mServer->on('managerStart', 
				function  () {
					\swoole_set_process_name($this->mServerName . '_manager');
				});
		$this->mServer->on('workerError', array(
			$this,
			'onWorkerError'
		));
	}

	public function onWorkerError ($serv, $worker_id, $worker_pid, $exit_code) {
		// 目前exit退出的错误码，在这儿拿不到。if (self::ERRCODE_DB_RECONNECT == $exit_code) {
		$this->initPoolService();
		// }
	}

	public function onRequest ($req, $resp) {
		$this->mOutputStream->setSwooleResponse($resp);
		$this->mApp->setOutputStream($this->mOutputStream);
		
		if (! $this->mApp->run(new HttpRequest($req))) {
			foreach ($this->mPoolArr as $pool) {
				$pool->releaseAll();
			}
		}
		
		if (0 !== $this->mNeedExitErrorCode) {
			exit($this->mNeedExitErrorCode);
		}
	}

	protected function initPoolService () {
		$serviceArr = Config::get('server.poolService');
		foreach ($serviceArr as $name => $cls) {
			$this->initOnePoolService($name, $cls);
		}
	}

	protected function initOnePoolService ($name, $cls = '') {
		$existsService = array();
		$moduleAliasArr = ModuleManager::Instance()->getAllModuleAlias();
		foreach ($moduleAliasArr as $alias) {
			$conf = Config::Instance()->getModuleConfig($alias, 'service.' . $name);
			if (empty($conf)) {
				continue;
			}
			$num = $conf['connections_num'];
			if (empty($num)) {
				$num = self::DEFAULT_CONNECTIONS_NUM;
			}
			
			// 不重复为多个相同配置建立重复连接
			$existsConf = $existsService[$name]['conf'];
			$existsNum = $existsService[$name]['num'];
			$tmpConf = $conf;
			unset($tmpConf['connections_num']);
			if ($existsConf == $tmpConf && $existsNum > 0 && $existsNum >= $num) {
				continue;
			} else {
				$existsService[$name]['conf'] = $tmpConf;
				$existsService[$name]['num'] = $num;
				
				$num = $num - $existsNum;
			}
			
			$pool = new ObjectPool();
			$pool->init(array(
				'num' => $num
			));
			$s = null;
			$sm = App::Instance()->getServiceManager();
			for ($i = 0; $i < $num; ++ $i) {
				$s = $sm->createService($name, $alias);
				$pool->addObject($i, $s);
			}
			
			if ('' == $cls) {
				$cls = 'Framework\Swoole\ObjectProxy';
			}
			$proxy = new $cls($pool);
			
			$keyName = $sm->getKeyName($name, $alias);
			$sm->addService($proxy, $keyName);
			
			$this->mPoolArr[] = $pool;
		}
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

	public function stop ($normal = true) {
		if (file_exists(self::$PID_FILE_PATH)) {
			$pid = file_get_contents(self::$PID_FILE_PATH);
			@\swoole_process::kill($pid);
			unlink(self::$PID_FILE_PATH);
		}
	}

	public function needExit ($errcode) {
		$this->mNeedExitErrorCode = $errcode;
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