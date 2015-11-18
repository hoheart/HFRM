<?php

namespace Framework\Facade;

use Framework\App;

class Event {

	static public function trigger ($event, $sender = null, $dataObject = null) {
		$em = App::Instance()->getService('event');
		$em->trigger($event, $sender, $dataObject);
	}
}