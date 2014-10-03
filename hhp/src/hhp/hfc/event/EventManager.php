<?php

namespace Hfc\Event;

use Hfc\Event\IEvent;

class EventManager {
	
	/**
	 * 配置
	 *
	 * @var array
	 */
	protected $mConfig = null;

	public function __construct (array $conf) {
		$this->mConfig = $conf;
	}

	/**
	 * 触发一个事件
	 *
	 * @param IEvent $event        	
	 * @return boolean 如果返回true，表示有人处理了这个事件，反之则没人处理。
	 */
	public function trigger (IEvent $event) {
		$ieventClsArr = $this->mConfig['Hfc\Event\IEvent'];
		
		$name = get_class($event);
		$clsArr = $this->mConfig[$name];
		
		return $this->triggerListener($event, $ieventClsArr, $clsArr);
	}

	public function triggerCommonEvent ($name, $sender, $dataObject = null) {
		$e = new CommonEvent($sender, $name, $dataObject);
		$ieventClsArr = $this->mConfig['Hfc\Event\IEvent'];
		$clsArr = $this->mConfig['Hfc\Event\CommonEvent'][$name];
		
		return $this->triggerListener($e, $ieventClsArr, $clsArr);
	}

	protected function triggerListener (IEvent $e, array $ieventClsArr = null, array $clsArr = null) {
		// 监听所有的时间的监听器，事件不终止，继续往下传递。
		if (null != $ieventClsArr) {
			foreach ($ieventClsArr as $listenerCls) {
				$l = $listenerCls::Instance();
				$l->handle($e);
			}
		}
		
		if (null != $clsArr) {
			foreach ($clsArr as $listenerCls) {
				$l = $listenerCls::Instance();
				if ($l->handle($e)) {
					return true;
				}
			}
		}
		
		return false;
	}
}
?>