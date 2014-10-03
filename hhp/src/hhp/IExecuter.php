<?php

namespace hhp;

interface IExecuter {

	static public function Instance ();

	public function run ($do = null);
}
?>