<?php

namespace Framework;

class Session {

	public function __construct () {
		$conf = Config::Instance();
		
		ini_set('session.save_handler', $conf->get('session.saveHandler'));
		ini_set('session.save_path', $conf->get('session.savePath'));
		ini_set('session.name', $conf->get('session.cookieName'));
		ini_set('session.cookie_secure', $conf->get('session.cookieSecure'));
		ini_set('session.gc_maxlifetime', $conf->get('session.gcMaxlifetime'));
		
		$sessionCookieDomain = $conf->get('session.cookieDomain');
		session_set_cookie_params(0, '/', $sessionCookieDomain);
		
		session_start();
	}

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new self();
		}
		
		return $me;
	}

	public function set ($key, $val) {
		$_SESSION[$key] = $val;
	}

	public function get ($key) {
		return $_SESSION[$key];
	}

	public function forget ($key) {
		unset($_SESSION[$key]);
	}

	public function has ($key) {
		return array_key_exists($key, $_SESSION);
	}
}
?>