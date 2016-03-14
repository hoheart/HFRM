<?php

namespace Framework;

interface IOutputStream {

	public function write ($str, $offset = 0, $count = -1);

	public function flush ();

	public function close ();
}