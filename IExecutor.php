<?php

namespace hhp;

interface IExecutor {

	static public function Instance ();

	public function run ($do = null);
}
?>