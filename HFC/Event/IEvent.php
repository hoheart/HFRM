<?php

namespace HFC\Event;

interface IEvent {

	public function __construct ($sender, $dataObject);

	public function getSender ();

	public function getDataObject ();
}
?>