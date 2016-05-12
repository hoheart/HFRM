<?php

namespace Framework\Exception;

class UserErrcode extends \HFC\Exception\UserErrcode {
	const ErrorOK = 0;
	const RequestError = 4100;
	const NotFoundHttp = 4104;
}