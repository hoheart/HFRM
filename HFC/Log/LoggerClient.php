<?php

namespace Framework\HFC\Log;

use Framework\Facade\Module;
use Framework\HFC\Log\Logger;
use Framework\IService;
use Framework\App;
use Framework\Config;
use Framework\RequestContext;

class LoggerClient implements IService {
	
	/**
	 *
	 * @var \AMQPConnection
	 */
	protected $mConnection = null;
	
	/**
	 *
	 * @var \AMQPExchange
	 */
	protected $mExchange = null;
	
	/**
	 *
	 * @var array $mConf
	 */
	protected $mConf = array();

	public function __construct () {
	}

	public function init (array $conf = array()) {
		$this->mConf = $conf;
		
		$this->mConnection = new \AMQPConnection($conf);
		$this->mConnection->connect();
		
		// 创建exchange名称和类型
		$channel = new \AMQPChannel($this->mConnection);
		$ex = new \AMQPExchange($channel);
		$ex->setName('EXCHANGE_LOG');
		$ex->setType(AMQP_EX_TYPE_DIRECT);
		$ex->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
		$ex->declareExchange();
		$this->mExchange = $ex;
		
		// 创建queue名称，使用exchange，绑定routingkey
		$q = new \AMQPQueue($channel);
		$q->setName('QUEUE_LOG');
		$q->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
		$q->declareQueue();
		$q->bind('EXCHANGE_LOG', '');
	}

	public function start () {
	}

	public function stop ($normal = true) {
	}

	public function __destruct () {
		$this->mConnection->disconnect();
	}

	public function log ($str, $type = Logger::LOG_TYPE_RUN, $level = Logger::LOG_LEVEL_FATAL, RequestContext $context = null) {
		$module = Config::Instance()->get('app.moduleDir');
		$moduleName = basename($module);
		
		$clientIp = '';
		if (null != $context) {
			$clientIp = $context->request->getClientIP();
		}
		$data = array(
			'type' => $type,
			'moduleName' => $moduleName,
			'desc' => $str,
			'level' => $level,
			'clientIp' => $clientIp,
			'machineName' => $this->mConf['localMachineName'],
			'platformId' => $this->mConf['platformId'],
			'createdTime' => date('Y-m-d H:i:s')
		);
		
		$this->mExchange->publish(json_encode($data), '');
	}
}
