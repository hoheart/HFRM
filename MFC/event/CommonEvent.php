<?php

namespace hfc\event;

/**
 * 用名字标志的普通事件。为方便使用，系统需要一个以名字标志的普通事件，不需要再写别的类，这样，减少了依赖。
 *
 * @author Hoheart
 *        
 */
class CommonEvent implements IEvent {
	
	/**
	 * 事件的名字
	 *
	 * @var unknown
	 */
	protected $mName = null;
	
	/**
	 * 事件的发起者。
	 *
	 * @var object
	 */
	protected $mSender = null;
	
	/**
	 * 事件的附加对象。
	 *
	 * @var object
	 */
	protected $mDataObject = null;

	public function __construct ($sender, $name = null, $dataObject = null) {
		$this->mName = $name;
		$this->mSender = $sender;
		$this->mDataObject = $dataObject;
	}

	public function __get ($name) {
		switch ($name) {
			case 'name':
				return $this->mName;
			case 'sender':
				return $this->mSender;
			case 'dataObject':
				return $this->mDataObject;
		}
	}

	public function getSender () {
		return $this->mSender;
	}
}
?>