<?php

namespace test\Hfc\Event;

use Hfc\Event\IEvent;

class Event implements IEvent {
	protected $mSender = null;

	public function __construct ($sender) {
		$this->mSender = $sender;
	}

	public function getSender () {
		return $this->mSender;
	}
}

class Event1 implements IEvent {
	protected $mSender = null;

	public function __construct ($sender) {
		$this->mSender = $sender;
	}

	public function getSender () {
		return $this->mSender;
	}

	public function output () {
		echo __CLASS__;
	}
}
?>