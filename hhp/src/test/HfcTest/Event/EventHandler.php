<?php

namespace test\Hfc\Event;

use Hfc\Event\IEventHandler;
use test\Controller\TestController;
use Hfc\Event\IEvent;

class EventHandler implements IEventHandler {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new EventHandler();
		}
		
		return $me;
	}

	public function handle (IEvent $event) {
		if (! $event->getSender() instanceof TestController) {
			return;
		}
		
		if ($event instanceof \Hfc\Event\CommonEvent) {
			echo $event->dataObject[1];
		}
		
		echo 0;
	}
}

class EventHandler1 implements IEventHandler {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new EventHandler1();
		}
		
		return $me;
	}

	public function handle (IEvent $event) {
		if (! $event->getSender() instanceof TestController) {
			return;
		}
		
		if ($event instanceof \Hfc\Event\CommonEvent) {
			echo $event->dataObject[0];
		}
		
		echo 1;
	}
}

class EventHandler2 implements IEventHandler {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new EventHandler2();
		}
		
		return $me;
	}

	public function handle (IEvent $event) {
		if (! $event->getSender() instanceof TestController) {
			return;
		}
		
		echo 2;
	}
}

class EventHandler3 implements IEventHandler {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new EventHandler3();
		}
		
		return $me;
	}

	public function handle (IEvent $event) {
		if (! $event->getSender() instanceof TestController) {
			return;
		}
		
		echo 3;
	}
}

class EventHandler4 implements IEventHandler {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new EventHandler4();
		}
		
		return $me;
	}

	public function handle (IEvent $event) {
		if (! $event->getSender() instanceof TestController) {
			return;
		}
		
		echo 4;
	}
}

class EventHandler5 implements IEventHandler {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new EventHandler5();
		}
		
		return $me;
	}

	public function handle (IEvent $event) {
		if (! $event->getSender() instanceof TestController) {
			return;
		}
		
		echo 5;
	}
}

class EventHandler6 implements IEventHandler {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new EventHandler6();
		}
		
		return $me;
	}

	public function handle (IEvent $event) {
		if (! $event->getSender() instanceof TestController) {
			return;
		}
		
		echo 6;
	}
}

class EventHandler7 implements IEventHandler {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new EventHandler7();
		}
		
		return $me;
	}

	public function handle (IEvent $event) {
		if (! $event->getSender() instanceof TestController) {
			return;
		}
		
		echo 7;
	}
}

class EventHandler8 implements IEventHandler {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new EventHandler8();
		}
		
		return $me;
	}

	public function handle (IEvent $event) {
		if (! $event->getSender() instanceof TestController) {
			return;
		}
		
		echo 8;
	}
}