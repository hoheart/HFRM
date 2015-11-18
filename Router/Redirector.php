<?php

namespace Framework\Router;

use Framework\App;
use Framework\Request\HttpRequest;

class Redirector {

	/**
	 * Create a new Redirector instance.
	 *
	 * @param URLGenerator $generator        	
	 * @return void
	 */
	public function __construct () {
	}

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new self();
		}
		
		return $me;
	}

	/**
	 * Create a new redirect response to the "home" route.
	 */
	public function home () {
		return $this->to('/');
	}

	/**
	 * Create a new redirect response to the previous location.
	 */
	public function back () {
		$req = App::Instance()->getRequest();
		$ref = $req->getHeader('referer');
		
		return $this->to($ref);
	}

	/**
	 * Create a new redirect response to the current URI.
	 */
	public function refresh (HttpRequest $req) {
		return $this->to($req->getURI());
	}

	/**
	 * Create a new redirect response to the given path.
	 *
	 * @param string $path        	
	 * @param int $status        	
	 * @param array $headers        	
	 * @param bool $secure        	
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function to ($path) {
		// 在exit之前，必须要stop
		$app = App::Instance();
		$app->stop();
		
		$req = $app->getRequest();
		if ($req->getResource() == $path) {
			exit(0);
		}
		
		header('Location: ' . $path, null, 302);
		
		exit(0);
	}
}
