<?php

namespace HFC\Event;

use HFC\Event\IEvent;
use HFC\Exception\ParameterErrorException;
use Framework\IService;

/**
 * 事件的监听者，都配置在service.event.config里，key是事件的类型，值是监听者的数组
 * 如果事件是一个子类，再触发完该类的监听者后，要再次出发父类的监听者。
 *
 * @author Hoheart
 *        
 */
class EventManager implements IService {
	
	/**
	 * 配置
	 *
	 * @var array
	 */
	protected $mConfig = null;

	public function __construct () {
	}

	public function init (array $conf) {
		$this->mConfig = $conf;
	}

	public function start () {
	}

	public function stop ($normal = true) {
	}

	/**
	 * 触发一个事件
	 *
	 * @param object $event
	 *        	可以是字符串，也可以是IEvent对象。字符串表示事件名称，是事件的唯一标志。
	 * @param object $sender        	
	 * @param object $dataObject        	
	 * @return boolean 如果返回true，表示有人处理了这个事件，反之则没人处理。
	 */
	public function trigger ($event, $sender = null, $dataObject = null) {
		if (is_string($event)) {
			return $this->triggerCommonEvent($event, $sender, $dataObject);
		} else {
			return $this->triggerListener(get_class($event), $event);
		}
	}

	public function triggerCommonEvent ($name, $sender, $dataObject = null) {
		if (null == $sender) {
			throw new ParameterErrorException('common event need sender.');
		}
		
		$e = new CommonEvent($sender, $name, $dataObject);
		$listenerArr = $this->mConfig['HFC\Event\CommonEvent'][$name];
		
		return $this->triggerListener('HFC\Event\CommonEvent', $e, $listenerArr);
	}

	protected function triggerListener ($clsName, IEvent $e, $listenerArr = array()) {
		if (empty($listenerArr)) {
			$listenerArr = $this->mConfig[$clsName];
		}
		if (empty($listenerArr)) {
			return false;
		}
		
		// 触发所有的事件的监听器，事件不终止，继续往下传递。
		foreach ($listenerArr as $listenerCls) {
			$l = $listenerCls::Instance();
			if ($l->handle($e)) {
				return true;
			}
		}
		
		// 如果事件是一个子类，再触发完该类的监听者后，要再次出发父类的监听者。
		$parentCls = class_parents($clsName);
		$parentImplements = class_implements($clsName);
		$clsArr = $parentCls + $parentImplements;
		foreach ($clsArr as $clsName) {
			if ($this->triggerListener($clsName, $e)) {
				return true;
			}
		}
		
		return false;
	}
}