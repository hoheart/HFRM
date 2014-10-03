<?php

namespace hhp;

abstract class Controller {
	protected $mRequest = null;

	public function __construct () {
	}

	public function setRequest (IRequest $request) {
		$this->mRequest = $request;
		
		return $this;
	}

	public function getRequest () {
		return $this->mRequest;
	}

	abstract public function getConfig ($actionName);
}

?>