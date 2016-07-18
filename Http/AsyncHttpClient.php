<?php

namespace Framework\App;

use Framework\Http\HttpRequest;

class AsyncHttpClient {

	static public function wait () {
		\Ev::run();
	}

	public function exec (HttpRequest $req, \Closure $fn) {
	}
}