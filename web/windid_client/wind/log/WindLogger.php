<?php
/**
 * 日志记录
 * 
 * 日志记录的级别有以下几种：
 * <ul>
 * <li>WindLogger::LEVEL_INFO = 1: 信息记录，只记录需要记录的信息。</li>
 * <li>WindLogger::LEVEL_TRACE = 2: 信息记录，同时记录来自trace的信息。</li>
 * <li>WindLogger::LEVEL_DEBUG = 3: 信息记录，同时记录来自trace的信息。</li>
 * <li>WindLogger::LEVEL_ERROR = 4: 记录错误信息，不包含trace信息。</li>
 * <li>WindLogger::LEVEL_PROFILE = 5: 分析信息记录，包含详细的时间及内存使用情况等</li>
 * </ul>
 * 日志的存放形式也可以通过write_type设置：
 * <ul>
 * <li>0: 打印全部日志信息结果</li>
 * <li>1: 按照level分文件存储日志记录</li>
 * <li>2: 按照type分文件存储日志记录</li>
 * </ul>
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindLogger.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package log
 */
class WindLogger extends WindModule {
	
	/**
	 * 级别1： 只是记录信息不记录trace信息
	 *
	 * @var int
	 */
	const LEVEL_INFO = 1;
	
	/**
	 * 级别2：将会打印出堆栈中trace信息
	 *
	 * @var int
	 */
	const LEVEL_TRACE = 2;
	
	/**
	 * 级别3：标志该信息是一个debug
	 * 
	 * debug信息将会导出trace信息和debug详细信息
	 *
	 * @var int
	 */
	const LEVEL_DEBUG = 3;
	
	/**
	 * 级别4：记录错误信息，不包含trace信息
	 *
	 * @var int
	 */
	const LEVEL_ERROR = 4;
	
	/**
	 * 级别5：分析信息记录，包含详细的时间及内存使用情况等
	 *
	 * @var int
	 */
	const LEVEL_PROFILE = 5;
	
	/**
	 * 日志的方式
	 *
	 * @var int
	 */
	const WRITE_TYPE = 2;
	
	/**
	 * 日志记录中profile信息开始的标志
	 *
	 * @var string
	 */
	const TOKEN_BEGIN = 'begin:';
	
	/**
	 * 日志记录中profile信息结束的标志
	 *
	 * @var string
	 */
	const TOKEN_END = 'end:';
	
	/**
	 * 每次当日志数量达到1000条的时候，就写入文件一次
	 * 
	 * @var int
	 */
	private $_autoFlush = 1000;
	
	/**
	 * 日志内容
	 *
	 * @var array
	 */
	private $_logs = array();
	
	/**
	 * 日志条数统计
	 *
	 * @var int
	 */
	private $_logCount = 0;
	
	/**
	 * 日志的详细信息
	 *
	 * @var array
	 */
	private $_profiles = array();
	
	/**
	 * 日志记录的地址
	 *
	 * @var string
	 */
	private $_logDir;
	
	/**
	 * 日志文件的最大长度
	 *
	 * @var int
	 */
	private $_maxFileSize = 100;
	
	/**
	 * 日志打印形式
	 * 
	 * 0: 打印全部日志信息结果
	 * 1: 按照level分文件存储日志记录
	 * 2: 按照type分文件存储日志记录
	 * 
	 * @var int
	 */
	private $_writeType = 0;
	
	/**
	 * 存放日志打印形式
	 *
	 * @var array
	 */
	private $_types = array();

	/**
	 * 构造函数
	 * 
	 * @param string $logDir 日志文件存放的目录
	 * @param int $writeType 日志文件的保存方式
	 * @return void
	 */
	public function __construct($logDir = '', $writeType = 0, $maxFileSize = 100) {
		$this->setLogDir($logDir);
		$this->_writeType = $writeType;
		$this->setMaxFileSize($maxFileSize);
	}

	/**
	 * 添加info级别的日志信息
	 * 
	 * @param string $msg 日志信息
	 * @param string $type 日志的类型,默认为wind.system
	 * @param boolean $flush 是否将日志输出到文件,为true的时候将写入文件,默认为false
	 * @return void
	 */
	public function info($msg, $type = 'wind.system', $flush = false) {
		$this->log($msg, self::LEVEL_INFO, $type, $flush);
	}

	/**
	 * 添加trace级别的日志信息
	 * 
	 * @param string $msg 日志信息
	 * @param string $type 日志的类型,默认为wind.system
	 * @param boolean $flush 是否将日志输出到文件,为true的时候将写入文件,默认为false
	 * @return void
	 */
	public function trace($msg, $type = 'wind.system', $flush = false) {
		$this->log($msg, self::LEVEL_TRACE, $type, $flush);
	}

	/**
	 * 添加debug级别的日志信息
	 * 
	 * @param string $msg 日志信息
	 * @param string $type 日志的类型,默认为wind.system
	 * @param boolean $flush 是否将日志输出到文件,为true的时候将写入文件,默认为false
	 * @return void
	 */
	public function debug($msg, $type = 'wind.system', $flush = false) {
		$this->log($msg, self::LEVEL_DEBUG, $type, $flush);
	}

	/**
	 * 添加error级别的日志信息
	 * 
	 * @param string $msg 日志信息
	 * @param string $type 日志的类型,默认为wind.core
	 * @param boolean $flush 是否将日志输出到文件,为true的时候将写入文件,默认为false
	 * @return void
	 */
	public function error($msg, $type = 'wind.core', $flush = false) {
		$this->log($msg, self::LEVEL_ERROR, $type, $flush);
	}

	/**
	 * 添加profile级别的开始位置日志信息
	 * 
	 * 通过该接口添加的日志信息将是记录一个开始位置的信息
	 * 
	 * @param string $msg 日志信息
	 * @param string $type 日志的类型,默认为wind.core
	 * @param boolean $flush 是否将日志输出到文件,为true的时候将写入文件,默认为false
	 * @return void
	 */
	public function profileBegin($msg, $type = 'wind.core', $flush = false) {
		$this->log('begin:' . trim($msg), self::LEVEL_PROFILE, $type, $flush);
	}

	/**
	 * 添加profile级别的结束位置日志信息
	 * 
	 * 通过该接口添加的日志信息将是记录一个结束位置的信息
	 * 
	 * @param string $msg 日志信息
	 * @param string $type 日志的类型,默认为wind.core
	 * @param boolean $flush 是否将日志输出到文件,为true的时候将写入文件,默认为false
	 * @return void
	 */
	public function profileEnd($msg, $type = 'wind.core', $flush = false) {
		$this->log('end:' . trim($msg), self::LEVEL_PROFILE, $type, $flush);
	}

	/**
	 * 添加info级别的日志信息
	 * 
	 * @param string $msg 日志信息
	 * @param int $level 日志记录的级别,默认为INFO级别即为1
	 * @param string $type 日志的类型,默认为wind.system
	 * @param boolean $flush 是否将日志输出到文件,为true则将会写入文件,默认为false
	 * @return void
	 */
	public function log($msg, $level = self::LEVEL_INFO, $type = 'wind.system', $flush = false) {
		if ($this->_writeType & self::WRITE_TYPE)
			(count($this->_types) >= 5 || $this->_logCount >= $this->_autoFlush) && $this->flush();
		else
			$this->_logCount >= $this->_autoFlush && $this->flush();
		if ($level === self::LEVEL_PROFILE)
			$message = $this->_build($msg, $level, $type, microtime(true), 
				$this->getMemoryUsage(false));
		elseif ($level === self::LEVEL_DEBUG)
			$message = $this->_build($msg, $level, $type, microtime(true));
		else
			$message = $this->_build($msg, $level, $type);
		$this->_logs[] = array($level, $type, $message);
		$this->_logCount++;
		if ($this->_writeType == self::WRITE_TYPE && !in_array($type, $this->_types)) $this->_types[] = $type;
		if ($flush) $this->flush();
	}

	/**
	 * 将记录的日志列表信息写入文件
	 * 
	 * @return boolean
	 */
	public function flush() {
		if (empty($this->_logs)) return false;
		Wind::import('WIND:utility.WindFile');
		$_l = $_logTypes = $_logLevels = array();
		$_map = array(
			self::LEVEL_INFO => 'info', 
			self::LEVEL_ERROR => 'error', 
			self::LEVEL_DEBUG => 'debug', 
			self::LEVEL_TRACE => 'trace', 
			self::LEVEL_PROFILE => 'profile');
		
		foreach ($this->_logs as $key => $value) {
			$_l[] = $value[2];
			$_logTypes[$value[1]][] = $value[2];
			$_logLevels[$value[0]][] = $value[2];
		}
		if ($this->_writeType & 1) {
			foreach ($_logLevels as $key => $value) {
				if (!$fileName = $this->_getFileName($_map[$key])) continue;
				WindFile::write($fileName, join("", $value), 'a');
			}
		}
		if ($this->_writeType & 2) {
			foreach ($_logTypes as $key => $value) {
				if (!$fileName = $this->_getFileName($key)) continue;
				WindFile::write($fileName, join("", $value), 'a');
			}
		}
		if ($fileName = $this->_getFileName()) {
			WindFile::write($fileName, join("", $_l), 'a');
		}
		$this->_logs = array();
		$this->_logCount = 0;
		return true;
	}

	/**
	 * 返回内存使用量
	 * 
	 * @param boolean $peak 是否是内存峰值,默认为true
	 * @return int
	 */
	public function getMemoryUsage($peak = true) {
		if ($peak && function_exists('memory_get_peak_usage'))
			return memory_get_peak_usage();
		elseif (function_exists('memory_get_usage'))
			return memory_get_usage();
		$pid = getmypid();
		if (strncmp(PHP_OS, 'WIN', 3) === 0) {
			exec('tasklist /FI "PID eq ' . $pid . '" /FO LIST', $output);
			return isset($output[5]) ? preg_replace('/[\D]/', '', $output[5]) * 1024 : 0;
		} else {
			exec("ps -eo%mem,rss,pid | grep $pid", $output);
			$output = explode("  ", $output[0]);
			return isset($output[1]) ? $output[1] * 1024 : 0;
		}
	}

	/**
	 * 组装日志信息
	 * 
	 * @param string $msg 日志信息
	 * @param int  $level 日志级别
	 * @param string  $type 日志类型
	 * @param int  $timer 日志记录的时间,默认为0
	 * @param int $mem 日志记录的时候内容使用情况,默认为0
	 * @return string 构造好的信息字符串
	 */
	private function _build($msg, $level, $type, $timer = 0, $mem = 0) {
		$result = '';
		switch ($level) {
			case self::LEVEL_INFO:
				$result = $this->_buildInfo($msg);
				break;
			case self::LEVEL_ERROR:
				$result = $this->_buildError($msg);
				break;
			case self::LEVEL_DEBUG:
				$result = $this->_buildDebug($msg);
				break;
			case self::LEVEL_TRACE:
				$result = $this->_buildTrace($msg);
				break;
			case self::LEVEL_PROFILE:
				$result = $this->_buildProfile($msg, $type, $timer, $mem);
				break;
			default:
				break;
		}
		return $result ? '[' . date('Y-m-d H:i:s') . '] ' . $result . "\r\n" : '';
	}

	/**
	 * 构造profile信息格式
	 * 
	 * @param string $msg 记录的信息
	 * @param string $type 记录的信息类型
	 * @param int $timer 记录的信息的时间
	 * @param int $mem 记录的信息的时候内容使用情况
	 * @return string 返回构造好的profile信息
	 */
	private function _buildProfile($msg, $type, $timer, $mem) {
		$_msg = '';
		if (strncasecmp($msg, self::TOKEN_BEGIN, strlen(self::TOKEN_BEGIN)) == 0) {
			$_token = substr($msg, strlen(self::TOKEN_BEGIN));
			$_token = substr($_token, 0, strpos($_token, ':'));
			$this->_profiles[] = array(
				$_token, 
				substr($msg, strpos($msg, ':', strlen(self::TOKEN_BEGIN)) + 1), 
				$type, 
				$timer, 
				$mem);
		} elseif (strncasecmp(self::TOKEN_END, $msg, strlen(self::TOKEN_END)) == 0) {
			$_msg = "PROFILE! Message:";
			$_token = substr($msg, strlen(self::TOKEN_END));
			$_token = substr($_token, 0, strpos($_token, ':'));
			foreach ($this->_profiles as $key => $profile) {
				if ($profile[0] !== $_token) continue;
				if ($profile[1])
					$_msg .= "\r\n\t" . $profile[1];
				else
					$_msg .= "\r\n\t" . substr($msg, strpos($msg, ':', strlen(self::TOKEN_END)) + 1);
				$_msg .= "\r\n\tTime:" . ($timer - $profile[3]) . "\r\n\tMem:" . ($mem - $profile[4]) . "\r\n\tType:$profile[2]";
				break;
				unset($this->_profiles[$key]);
			}
		}
		return $_msg;
	}

	/**
	 * 组装info级别的信息输出格式
	 * 
	 * <code>
	 * INFO! Message: $msg
	 * </code>
	 * 
	 * @param string $msg 输出的信息
	 * @return string
	 */
	private function _buildInfo($msg) {
		return "INFO! Message:  " . $msg;
	}

	/**
	 * 组装堆栈trace的信息输出格式
	 * 
	 * <code>
	 * TRACE! Message: $msg
	 * #1 trace1
	 * #2 trace2
	 * </code>
	 * 
	 * @param string $msg 输出的信息
	 * @return string
	 */
	private function _buildTrace($msg) {
		return "TRACE! Message:  " . $msg . implode("\r\n", $this->_getTrace());
	}

	/**
	 * 组装debug信息输出
	 * 
	 * <code>
	 * DEBUG! Message: $msg
	 * #1 trace1
	 * #2 trace2
	 * </code>
	 * 
	 * @param string $msg 输出的信息
	 * @return string
	 */
	private function _buildDebug($msg) {
		return 'DEBUG! Message:  ' . $msg . implode("\r\n", $this->_getTrace());
	}

	/**
	 *组装Error信息输出
	 *
	 * <code>
	 * ERROR! Message: $msg
	 * #1 trace1
	 * #2 trace2
	 * </code>
	 * 
	 * @param string $msg 输出的错误信息
	 * @return string
	 */
	private function _buildError($msg) {
		return 'ERROR! Message:  ' . $msg;
	}

	/**
	 * 错误堆栈信息的获取及组装输出
	 * 
	 * <code>
	 * #1 trace
	 * #2 trace
	 * </code>
	 * 
	 * @return string
	 */
	private function _getTrace() {
		$num = 0;
		$info[] = 'Stack trace:';
		$traces = debug_backtrace();
		foreach ($traces as $traceKey => $trace) {
			if ($num >= 7) break;
			if ((isset($trace['class']) && $trace['class'] == __CLASS__) || isset($trace['file']) && strrpos(
				$trace['file'], __CLASS__ . '.php') !== false) continue;
			$file = isset($trace['file']) ? $trace['file'] . '(' . $trace['line'] . '): ' : '[internal function]: ';
			$function = isset($trace['class']) ? $trace['class'] . $trace['type'] . $trace['function'] : $trace['function'];
			if ($function == 'WindBase::log') continue;
			$args = array_map(array($this, '_buildArg'), $trace['args']);
			$info[] = '#' . ($num++) . ' ' . $file . $function . '(' . implode(',', $args) . ')';
		}
		return $info;
	}

	/**
	 * 组装输出的trace中的参数组装
	 * 
	 * @param mixed $arg 需要组装的信息
	 * @return string 返回组装好的信息
	 * <ul>
	 * <li>如果是array: 返回 Array</li>
	 * <li>如果是Object: 返回 Object classname</li>
	 * <li>其他格式: 返回 $arg</li>
	 * </ul>
	 */
	private function _buildArg($arg) {
		switch (gettype($arg)) {
			case 'array':
				return 'Array';
				break;
			case 'object':
				return 'Object ' . get_class($arg);
				break;
			default:
				return "'" . $arg . "'";
				break;
		}
	}

	/**
	 * 取得日志文件名
	 * 
	 * @param string $suffix 日志文件的后缀,默认为空
	 * @return string 返回日志文件名
	 */
	private function _getFileName($suffix = '') {
		$_maxsize = ($this->_maxFileSize ? $this->_maxFileSize : 100) * 1024;
		$_logfile = $this->_logDir . '/log' . ($suffix ? '_' . $suffix : '') . '.txt';
		if (is_file($_logfile) && $_maxsize <= filesize($_logfile)) {
			do {
				$_newFile = $this->_logDir . '/log' . ($suffix ? '_' . $suffix : '') . '_' . time() . '.txt';
			} while (is_file($_newFile));
			@rename($_logfile, $_newFile);
		}
		return $_logfile;
	}

	/**
	 * 设置日志保存的路径
	 * 
	 * @param string $logFile 日志保存的路径
	 * @return void
	 */
	public function setLogDir($logDir) {
		$this->_logDir = Wind::getRealDir($logDir);
		WindFolder::mkRecur($this->_logDir);
	}

	/**
	 * 设置日志文件最大的大小
	 * 
	 * @param int $_maxFileSize 文件的最大值
	 * @return void
	 */
	public function setMaxFileSize($maxFileSize) {
		$this->_maxFileSize = (int) $maxFileSize;
	}
}	