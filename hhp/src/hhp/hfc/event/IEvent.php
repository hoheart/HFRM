<?php

namespace Hfc\Event;

interface IEvent {

	public function __construct ($sender);

	public function getSender ();
}
?>