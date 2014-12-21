<?php

namespace hfc\exception;

class SystemErrcode {
	const SystemAPIError = 5000;
	const ClassNotFound = 5001;
	const NotImplemented = 5002;
	const DatabaseQuery = 5003;
	const DatabaseConnect = 5004;
	const FileNotFound = 5005;
	const MethodCallError = 5006; // 函数调用错误，不应该调用该函数。
}
?>