<?php

namespace hfc\Event;

use hfc\event\IEvent;
use hfc\exception\ParameterErrorException;

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
	 * @param object $event
	 *        	可以是字符串，也可以是IEvent对象。字符串表示时间名称，即事件的唯一标志。
	 * @param object $sender        	
	 * @param object $dataObject        	
	 * @return boolean 如果返回true，表示有人处理了这个事件，反之则没人处理。
	 */
	public function trigger ($event, $sender = null, $dataObject = null) {
		if (is_string($event)) {
			if (null == $sender) {
				throw new ParameterErrorException('common event need sender.');
			}
			return $this->triggerCommonEvent($event, $sender, $dataObject);
		}
		
		$ieventClsArr = $this->mConfig['hfc\event\IEvent'];
		
		$name = get_class($event);
		$clsArr = $this->mConfig[$name];
		
		return $this->triggerListener($event, $ieventClsArr, $clsArr);
	}

	public function triggerCommonEvent ($name, $sender, $dataObject = null) {
		$e = new CommonEvent($sender, $name, $dataObject);
		$ieventClsArr = $this->mConfig['hfc\event\IEvent'];
		$clsArr = $this->mConfig['hfc\event\CommonEvent'][$name];
		
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