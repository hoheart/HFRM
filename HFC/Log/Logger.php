<?php

namespace Framework\HFC\Log;

use Framework\Exception\ConfigErrorException;
use Framework\HFC\Exception\SystemAPIErrorException;
use Framework\IService;

class LogInfo {
	
	/**
	 *
	 * @var string
	 */
	public $platformName = '';
	public $ip = '';
	public $modulePath = '';
	public $logType = Logger::LOG_TYPE_RUN;
	public $content = '';
	public $size = 0;
	public $time = '';

	public function __construct ($str, $type, $modulePath, $level, $now, $platformName = '', $ip = '') {
		$this->platformName = empty($platformName) ? 'local' : $platformName;
		$this->ip = empty($ip) ? 'local' : $ip;
		
		$levelStr = '';
		if (Logger::LOG_TYPE_ERROR == $type) {
			switch ($level) {
				case Logger::LOG_LEVEL_FATAL:
					$levelStr = 'Fatal';
					break;
				case Logger::LOG_LEVEL_ERROR:
					$levelStr = 'Error';
					break;
				case Logger::LOG_LEVEL_WARN:
					$levelStr = 'Warning';
					break;
			}
			
			$levelStr = '[' . $levelStr . ']';
		}
		
		$modulePath = trim($modulePath, '/\\');
		$modulePath = str_replace('\\', '_', $modulePath);
		$modulePath = str_replace('/', '_', $modulePath);
		$this->modulePath = $modulePath;
		
		$this->logType = $type;
		
		$timeArr = explode(".", $now);
		$timeStr = '[' . date('Y-m-d H:i:s', $now) . '.' . $timeArr[1] . ']';
		$this->content = $timeStr . $levelStr . ' ' . $str;
		
		$this->size = strlen($this->content);
		
		$this->time = $now;
	}
}

/**
 * 记录日志。只是简单的记录文字日志，提供缓存机制，并定时定量写入磁盘。
 * 由于PHP的单进程特性，定时写入功能不精确，需要外界对该类调用触发。
 *
 * 日志先存入日志根目录的临时文件下，文件大小达到最大值后或一天结束，再把日志文件在放入所属日期目录。
 *
 * @author Hoheart
 *        
 */
class Logger implements IService {
	
	/**
	 * 日志类型
	 *
	 * @var integer
	 */
	const LOG_TYPE_RUN = 1;
	const LOG_TYPE_OPERATION = 2;
	const LOG_TYPE_SECURITY = 3;
	const LOG_TYPE_ERROR = 4;
	const LOG_TYPE_DEBUG = 5;
	
	/**
	 * 日志级别。
	 * 其实操作日志、运行日志都不太好划分级别，所以，只对错误日志划分了级别。其他日志采用尽量多记的原则。
	 *
	 * @var integer
	 */
	const LOG_LEVEL_FATAL = 1; // 每个严重的错误事件将会导致应用程序的退出。
	const LOG_LEVEL_ERROR = 2; // 虽然发生错误事件，但仍然不影响系统的继续运行。
	const LOG_LEVEL_WARN = 3; // 表明会出现潜在错误的情形。
	
	/**
	 * 日志文件后缀名
	 *
	 * @var string
	 */
	const LOG_FILE_EXTNAME = 'log';
	
	/**
	 * 用于记录上次移动临时文件的日期。
	 *
	 * @var string
	 */
	const TEMP_FILE_MOVE_LOCK = 'tempFileMove.lock';
	
	/**
	 * 日志缓存列表
	 *
	 * @var array
	 */
	protected $mBuffer = array();
	
	/**
	 * 缓存的日志大小
	 *
	 * @var integer
	 */
	protected $mBufferSize = 0;
	
	/**
	 * 上次写入磁盘事件
	 *
	 * @var integer
	 */
	protected $mLastTime = null;
	
	/**
	 * 配置
	 * 格式：参见配置文件
	 *
	 * @var array
	 *
	 */
	protected $mConf = null;

	/**
	 * 构造器
	 *
	 * $conf => array(
	 * // 由于日志文件很可能与其他数据文件，所以一般单独指定文件夹。
	 * 'root_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'log' .
	 * DIRECTORY_SEPARATOR,
	 * // 缓存大小，单位byte
	 * 'buffer_size' => 50000,
	 * // 写入文件事件间隔
	 * 'interval' => 30,
	 * // 是否记录调试日志。
	 * 'debug_log' => false,
	 * // 每个日志文件的大小，单位m
	 * 'file_size' => 50,
	 * // 是否启用日志记录
	 * 'enable' => true
	 * )
	 */
	public function __construct () {
	}

	public function init (array $conf) {
		$this->mConf = $conf;
	}

	public function start () {
	}

	public function stop ($normal = true) {
		$this->writeBuffer2File(null);
		$this->movePreviousTempFile();
	}

	public function __destruct () {
		$this->stop();
	}

	/**
	 * 把指定的文件移入日期文件夹。
	 *
	 * @param string $path        	
	 * @param string $dateDirRoot        	
	 * @param string $dateStr
	 *        	应该移动到的日期文件夹对应日期内的任意时刻。
	 */
	protected function moveFile2DateDir ($path, $dateDirRoot, $dateStr) {
		$dateDir = $dateDirRoot . $dateStr . DIRECTORY_SEPARATOR;
		if (! file_exists($dateDir)) {
			if (! mkdir($dateDir)) {
				throw new ConfigErrorException("config dir can not be write, dir:$dateDirRoot.");
			}
		}
		
		$suffix = '';
		for ($i = 0; true; ++ $i) {
			$newPath = $dateDir . basename($path, '.log') . date('YmdHis') . $suffix . '.' . self::LOG_FILE_EXTNAME;
			if (! rename($path, $newPath)) {
				if (! file_exists($newPath)) {
					throw new SystemAPIErrorException("can not rename $path to $newPath");
				}
			} else {
				break;
			}
			
			$suffix = '_' . $i;
		}
	}

	/**
	 * 把一条日志写入文件
	 *
	 * @param LogInfo $logInfo        	
	 * @param array $conf        	
	 */
	protected function writeOneLog (LogInfo $logInfo, array $conf) {
		$typeName = '';
		switch ($logInfo->logType) {
			case self::LOG_TYPE_RUN:
				$typeName = 'run';
				break;
			case self::LOG_TYPE_DEBUG:
				$typeName = 'debug';
				break;
			case self::LOG_TYPE_ERROR:
				$typeName = 'error';
				break;
			case self::LOG_TYPE_OPERATION:
				$typeName = 'operation';
				break;
			case self::LOG_TYPE_SECURITY:
				$typeName = 'security';
				break;
			default:
				$typeName = 'run';
				break;
		}
		$logFilePath = $this->getLogDir($conf, $logInfo);
		if (! file_exists($logFilePath)) {
			$this->createLogDir($conf, $logInfo);
		}
		
		$logFilePath .= $logInfo->modulePath . '[' . $typeName . '].' . self::LOG_FILE_EXTNAME;
		clearstatcache(true, $logFilePath);
		if (file_exists($logFilePath)) {
			if ((filesize($logFilePath) + $logInfo->size) > ($conf['file_size'] * 1048576)) { // 配置文件的单位是M
				$dateStr = date('Y-m-d', $logInfo->time);
				$this->moveFile2DateDir($logFilePath, dirname($logFilePath) . DIRECTORY_SEPARATOR, $dateStr);
			}
		}
		
		$fp = fopen($logFilePath, 'a+');
		if (false === $fp) {
			throw new ConfigErrorException("log config dir: {$conf['root_dir']} can not be write.");
		}
		flock($fp, LOCK_EX);
		$ret = fwrite($fp, $logInfo->content . "\r\n");
		if (false === $ret || $ret != $logInfo->size + 2) {
			flock($fp, LOCK_UN);
			throw new SystemAPIErrorException("write to file: $logFilePath error.return values: $ret");
		}
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	protected function getLogDir ($conf, $logInfo) {
		$logFilePath = $conf['root_dir'];
		
		if ('' != $logInfo->platformName) {
			$logFilePath .= $logInfo->platformName . DIRECTORY_SEPARATOR;
		}
		
		// if ('' != $logInfo->ip) {
		// $logFilePath .= $logInfo->ip . DIRECTORY_SEPARATOR;
		// }
		
		return $logFilePath;
	}

	protected function createLogDir ($conf, $logInfo) {
		$logDir = $this->getLogDir($conf, $logInfo);
		if (! file_exists($logDir)) {
			if (false === mkdir($logDir)) {
				throw new ConfigErrorException('can not create folder:' . $logDir);
			}
		}
	}

	/**
	 * 把缓存中的日志写入磁盘
	 *
	 * @param integer $now        	
	 */
	protected function writeBuffer2File ($now) {
		while (count($this->mBuffer) > 0) {
			$this->writeOneLog($this->mBuffer[0], $this->mConf, $this->mLastTime);
			
			$this->mBufferSize -= $this->mBuffer[0]->size;
			
			array_shift($this->mBuffer);
		}
		
		$this->mLastTime = $now;
	}

	protected function movePreviousTempFile () {
		// 因为php的一次性运行的特点，所以，每次写入时，都要检查有没有昨天及以前的文件有没有移入指定文件夹。
		// 本来也可以放在每个文件有新内容时再移动效率高很多，但如果某种类型的日志之后一直没有新的，
		// 则其之前的文件将无法移入日期文件夹，
		// 有悖于需求(需求是:新的一天，把原来所有的临时文件移入前一天的文件夹)。以功能完善优先。
		$lockFile = $this->mConf['root_dir'] . self::TEMP_FILE_MOVE_LOCK;
		$fp = fopen($lockFile, 'c+');
		if (false === $fp) {
			throw new SystemAPIErrorException('create file for logger error. file:' . $lockFile);
		}
		$todayDate = date('Y-m-d');
		flock($fp, LOCK_EX);
		$lastMoveDate = fread($fp, 10);
		if ($lastMoveDate == $todayDate) { // 用这个方式保证一天只移动一次。
			flock($fp, LOCK_UN);
			fclose($fp);
			return;
		}
		
		$rootDir = $this->mConf['root_dir'];
		$rootDP = opendir($this->mConf['root_dir']);
		while (false !== ($platformName = readdir($rootDP))) {
			if ('..' == $platformName || '.' == $platformName || self::TEMP_FILE_MOVE_LOCK == $platformName) {
				continue;
			}
			
			$fileDir = $rootDir . $platformName . DIRECTORY_SEPARATOR;
			$platformDP = opendir($fileDir);
			while (false !== ($fileName = readdir($platformDP))) {
				if ('..' == $fileName || '.' == $fileName) {
					continue;
				}
				
				$filePath = $fileDir . $fileName;
				if (is_dir($filePath)) {
					continue;
				}
				
				$this->moveFile2DateDir($filePath, $fileDir, $lastMoveDate);
			}
			closedir($platformDP);
		}
		closedir($rootDP);
		
		ftruncate($fp, 0);
		fseek($fp, 0, SEEK_SET);
		fwrite($fp, $todayDate);
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	public function operationLog ($moduleAlias, $ctrlClassName, $actionName, $operationName, $opResult, $desc) {
		$data = array(
			'type' => Logger::LOG_TYPE_OPERATION,
			'operatorId' => null,
			'moduleName' => $moduleAlias,
			'controllerName' => $ctrlClassName,
			'actionName' => $actionName,
			'operationName' => $operationName,
			'result' => $opResult,
			'sessionId' => null,
			'desc' => $desc,
			'clientIp' => null,
			'platformId' => null
		);
		$this->log(json_encode($data), Logger::LOG_TYPE_OPERATION, $moduleAlias);
	}

	/**
	 * 记录日志。
	 * 实现时，操作日志、运行日志都没有级别，只对错误日志划分了级别。
	 *
	 * @param string $str        	
	 * @param integer $type        	
	 * @param string $modulePath
	 *        	该条日志是由哪个系统哪个机器哪个模块模块产生的。日志将按这个路径进行存储。
	 *        	格式为：系统名称:机器IP:模块路径。
	 *        	如果为空，表示是系统日志。
	 *        	如果路径是嵌套在另一个模块下，用子文件夹（'asdf/adsf'）的形式表示。
	 * @param integer $level        	
	 */
	function log ($str, $type = self::LOG_TYPE_RUN, $modulePath = '', $level = self::LOG_LEVEL_ERROR, $platformName = '', $ip = '') {
		if (! $this->mConf['enable']) {
			return;
		}
		if (self::LOG_TYPE_DEBUG == $type) {
			if (! $this->mConf['debug_log']) {
				return;
			}
		}
		
		$now = microtime(true);
		// 如果是前一天的日志还没写入，写入文件。
		if (null === $this->mLastTime || $this->mLastTime < strtotime(date('Y-m-d', $now))) {
			$this->writeBuffer2File($now);
			$this->movePreviousTempFile();
		}
		
		// 如果到了配置的时间，把日志写入文件。
		if (($now - $this->mLastTime) >= $this->mConf['interval']) {
			$this->writeBuffer2File($now);
		}
		
		// 先把这条放进去，该写入磁盘时直接写入，保证日志及时写入。
		$logInfo = new LogInfo($str, $type, $modulePath, $level, $now, $platformName, $ip);
		$this->mBuffer[] = $logInfo;
		$this->mBufferSize += $logInfo->size;
		if ($this->mBufferSize > $this->mConf['buffer_size']) {
			$this->writeBuffer2File($now);
		}
	}
}