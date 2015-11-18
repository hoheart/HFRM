<?php

namespace HFC\Event;

interface IEventHandler {

	public function handle (IEvent $event);
}
?>