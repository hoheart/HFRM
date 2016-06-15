<?php

namespace Framework\Exception;

class SystemErrcode extends \Framework\HFC\Exception\SystemErrcode {
	const ModuleNotEnable = 5101;
	const ConfigError = 5102;
	const APINotAvailable = 5103;
	const RPCServiceError = 5104;
	const EndService = 5200;
}