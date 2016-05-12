<?php

namespace test\HfcTest\UtilTest;

use hfc\util\Logger as HLogger;
use hfc\util\LogInfo;
use hfc\io\Directory as HDirectory;
use hfc\io\File as HFile;
use test\AbstractTest;
use hhp\App;

class LoggerTest extends AbstractTest {

	public function test () {
		$this->loginfo();
		$this->constructAndDestruct();
		$this->tempFileFull();
		$this->newDay();
		$this->logType();
		$this->buffer();
		$this->interval();
		$this->debugLog();
		$this->enable();
	}

	public function loginfo () {
		$logType = HLogger::LOG_TYPE_ERROR;
		$modulePath = 'system/logger';
		$level = HLogger::LOG_LEVEL_ERROR;
		
		$now = strtotime('2014-12-12 23:22:01') . '.01';
		$info = new LogInfo('info', $logType, $modulePath, $level, $now);
		$content = '[2014-12-12 23:22:01.01][Error] info';
		$contentSize = strlen($content);
		if ($info->logType != $logType || $content != $info->content ||
				 'system_logger' != $info->modulePath || $info->size != $contentSize ||
				 $info->time != $now) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		return true;
	}

	protected function getConf () {
		$app = App::Instance();
		return $app->getConfigValue('service')['log']['config'];
	}

	/**
	 * 只要往指定的文件夹寸东西了，就说明construct接受了配置。
	 *
	 * @return boolean
	 */
	public function constructAndDestruct () {
		$conf = $this->getConf();
		$dir = new HDirectory($conf['root_dir']);
		try {
			$dir->create();
		} catch (\Exception $e) {
		}
		$dir->clear();
		
		$logger = new HLogger($conf);
		$logger->log('constructor test', HLogger::LOG_TYPE_ERROR, 'init', HLogger::LOG_LEVEL_ERROR);
		
		$f = $conf['root_dir'] . 'init[Error].log';
		
		if (file_exists($f)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		unset($logger);
		
		if (! file_exists($f)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dir->unlink(true);
	}

	public function tempFileFull () {
		$conf = $this->getConf();
		$dir = new HDirectory($conf['root_dir']);
		try {
			$dir->create();
		} catch (\Exception $e) {
		}
		$dir->clear();
		
		$conf['file_size'] = 0.0009; // 1M
		
		$logger = new HLogger($conf);
		for ($i = 0; $i < 100; ++ $i) {
			$logger->log('constructor test.', HLogger::LOG_TYPE_ERROR, 'init', 
					HLogger::LOG_LEVEL_ERROR);
		}
		
		unset($logger);
		
		$dateDir = $conf['root_dir'] . date('Y-m-d') . DIRECTORY_SEPARATOR;
		if (! HFile::SExists($dateDir)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$d = new HDirectory($dateDir);
		$fileCount = 0;
		foreach ($d as $name) {
			$fpath = $dateDir . $name;
			if (HFile::SSize($fpath) > $conf['file_size'] * 1024 * 1024) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			++ $fileCount;
		}
		unset($d);
		
		if ($fileCount < 5) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dir->unlink(true);
	}

	public function newDay () {
		$conf = $this->getConf();
		$dir = new HDirectory($conf['root_dir']);
		try {
			$dir->create();
		} catch (\Exception $e) {
		}
		$dir->clear();
		
		// 测试只移动一个文件的情况
		$logger = new HLogger($conf);
		$logger->log('constructor test.', HLogger::LOG_TYPE_ERROR, 'init', HLogger::LOG_LEVEL_ERROR);
		$this->setSysTime2ToTomorrow();
		$logger->log('constructor test.', HLogger::LOG_TYPE_ERROR, 'init', HLogger::LOG_LEVEL_ERROR);
		$this->setSysTime2Yesterday();
		$movedCount = 0;
		$dateDir = $conf['root_dir'] . date('Y-m-d') . DIRECTORY_SEPARATOR;
		$d = new HDirectory($dateDir);
		foreach ($d as $name) {
			++ $movedCount;
		}
		if (1 !== $movedCount) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		unset($d);
		
		// 测试移动别的文件的情况。
		$logger = new HLogger($conf);
		$dir->clear();
		$logger->log('constructor test.', HLogger::LOG_TYPE_ERROR, 'init', HLogger::LOG_LEVEL_ERROR);
		unset($logger);
		$logger = new HLogger($conf);
		$this->setSysTime2ToTomorrow();
		$logger->log('movetodate test.', HLogger::LOG_TYPE_RUN, 'user');
		unset($logger);
		$this->setSysTime2Yesterday();
		$movedCount = 0;
		$d = new HDirectory($dateDir);
		foreach ($d as $name) {
			++ $movedCount;
		}
		if (1 !== $movedCount) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		unset($d);
		
		$conf = $this->getConf();
		$dir = new HDirectory($conf['root_dir']);
		$dir->unlink(true);
	}

	protected function setSysTime2ToTomorrow () {
		$t = time() + 86400;
		// 要用能修改系统时间的用户执行。
		if (PHP_OS == 'WINNT') { // windowsNT系列
			$d = date('Y-m-d', $t);
			`date $d`;
		} else {
			$d = date('mdHiy', $t);
			$cmdPath = App::$ROOT_DIR . 'test/bin/chdate.sh';
			$cmd = "sudo $cmdPath $d";
			`$cmd`;
		}
	}

	protected function setSysTime2Yesterday () {
		if (PHP_OS == 'WINNT') {
			$d = date('Y-m-d', time() - 86400);
			`date $d`;
		} else {
			$cmdPath = App::$ROOT_DIR . 'test/bin/chdate.sh';
			$d = date('mdHiy', time() - 86400);
			`sudo $cmdPath $d`;
		}
	}
	
	// 测试基本日志功能。包括每种类型都要测试。
	public function logType () {
		$conf = $this->getConf();
		$conf['debug_log'] = true;
		$logger = new HLogger($conf);
		
		$dir = new HDirectory($conf['root_dir']);
		try {
			$dir->create();
		} catch (\Exception $e) {
		}
		$dir->clear();
		
		$logTypeArr = array(
			HLogger::LOG_TYPE_DEBUG,
			HLogger::LOG_TYPE_ERROR,
			HLogger::LOG_TYPE_OPERATION,
			HLogger::LOG_TYPE_RUN,
			HLogger::LOG_TYPE_SECURITY
		);
		
		foreach ($logTypeArr as $logType) {
			$logger->log('test type.', $logType, 'user', HLogger::LOG_LEVEL_FATAL);
		}
		
		unset($logger);
		
		$fileNameArr = array(
			'user[debug].log',
			'user[error].log',
			'user[operation].log',
			'user[run].log',
			'user[security].log',
			HLogger::TEMP_FILE_MOVE_LOCK
		);
		$tempFileCount = 0;
		foreach ($dir as $name) {
			++ $tempFileCount;
			if (! in_array($name, $fileNameArr)) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}
		if (count($fileNameArr) != $tempFileCount) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dir->unlink(true);
	}

	public function buffer () {
		$conf = $this->getConf();
		$conf['buffer_size'] = 100;
		$logger = new HLogger($conf);
		
		$dir = new HDirectory($conf['root_dir']);
		try {
			$dir->create();
		} catch (\Exception $e) {
		}
		$dir->clear();
		
		$logger->log('test buffer.', HLogger::LOG_TYPE_RUN, 'user', HLogger::LOG_LEVEL_FATAL);
		$logger->log('test buffer.', HLogger::LOG_TYPE_RUN, 'user', HLogger::LOG_LEVEL_FATAL);
		
		$tmpFile = $conf['root_dir'] . 'user[run].log';
		if (file_exists($tmpFile)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$logger->log('test buffer.', HLogger::LOG_TYPE_RUN, 'user', HLogger::LOG_LEVEL_FATAL);
		
		if (! file_exists($tmpFile) || filesize($tmpFile) < 90) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		unset($logger);
		
		$dir->unlink(true);
	}

	public function interval () {
		$conf = $this->getConf();
		$conf['buffer_size'] = 100;
		$conf['interval'] = 1;
		$logger = new HLogger($conf);
		
		$dir = new HDirectory($conf['root_dir']);
		try {
			$dir->create();
		} catch (\Exception $e) {
		}
		$dir->clear();
		
		$logger->log('test buffer.', HLogger::LOG_TYPE_RUN, 'user', HLogger::LOG_LEVEL_FATAL);
		sleep(1);
		$logger->log('test buffer.', HLogger::LOG_TYPE_RUN, 'user', HLogger::LOG_LEVEL_FATAL);
		
		$tmpFile = $conf['root_dir'] . 'user[run].log';
		if (! file_exists($tmpFile)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		unset($logger);
		
		$dir->unlink(true);
	}

	public function debugLog () {
		$conf = $this->getConf();
		$conf['debug_log'] = false;
		$logger = new HLogger($conf);
		
		$dir = new HDirectory($conf['root_dir']);
		try {
			$dir->create();
		} catch (\Exception $e) {
		}
		$dir->clear();
		
		$logger->log('test debug log.', HLogger::LOG_TYPE_DEBUG, 'user', HLogger::LOG_LEVEL_FATAL);
		
		unset($logger);
		
		$tmpFile = $conf['root_dir'] . 'user[debug].log';
		if (file_exists($tmpFile)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dir->unlink(true);
	}

	public function enable () {
		$conf = $this->getConf();
		$conf['enable'] = false;
		$logger = new HLogger($conf);
		
		$dir = new HDirectory($conf['root_dir']);
		try {
			$dir->create();
		} catch (\Exception $e) {
		}
		$dir->clear();
		
		$logger->log('test debug log.', HLogger::LOG_TYPE_DEBUG, 'user', HLogger::LOG_LEVEL_FATAL);
		
		unset($logger);
		
		$tmpFile = $conf['root_dir'] . 'user[debug].log';
		if (file_exists($tmpFile)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dir->unlink(true);
	}
}
?>