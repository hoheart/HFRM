<?php

namespace Framework\HFC\Event;

interface IEventHandler {

	public function handle (IEvent $event);
}