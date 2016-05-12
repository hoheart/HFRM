<?php

namespace Hfc\Event;

interface IEventHandler {

	public function handle (IEvent $event);
}
?>