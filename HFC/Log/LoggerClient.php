<?php

namespace HFC\Log;

use Framework\Facade\Module;
use HFC\Log\Logger;
use Framework\IService;
use Framework\App;
use Framework\Module\ModuleManager;

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

	public function init (array $conf) {
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

	public function stop () {
	}

	public function __destruct () {
		$this->mConnection->disconnect();
	}

	public function operationLog ($moduleName, $controllerName, $actionName, $operationName, $result, $desc) {
		$operatorId = - 1;
		if (ModuleManager::Instance()->isModuleEnable('user')) {
			$operatorId = Module::getService('user', 'User\API\ILogin')->getLoginedUserId();
		}
		
		$data = array(
			'type' => Logger::LOG_TYPE_OPERATION,
			'operatorId' => $operatorId,
			'moduleName' => $moduleName,
			'controllerName' => $controllerName,
			'actionName' => $actionName,
			'operationName' => $operationName,
			'result' => $result,
			'sessionId' => session_id(),
			'desc' => $desc,
			'clientIp' => App::Instance()->getRequest()->getClientIP(),
			'machineName' => $this->mConf['localMachineName'],
			'platformId' => $this->mConf['platformId'],
			'createdTime' => date('Y-m-d H:i:s')
		);
		
		$this->mExchange->publish(json_encode($data), '');
	}

	public function log ($str, $type = Logger::LOG_TYPE_RUN, $modulePath = '', $level = Logger::LOG_LEVEL_FATAL) {
		$clientIp = '';
		$req = App::Instance()->getRequest();
		if( null != $req ){
			$clientIp = $req->getClientIP();
		}
		$data = array(
			'type' => $type,
			'moduleName' => $modulePath,
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
