<?php
/**
 * 错误处理句柄
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package wind.base
 */
abstract class WindError extends WindModule {
	protected $errorDir;
	protected $isClosed;

	/**
	 * 构造方法
	 * 
	 * @param string $errorDir 错误目录地址
	 * @param boolean $isClosed 站点是否关闭
	 */
	public function __construct($errorDir, $isClosed) {
		$this->errorDir = $errorDir;
		$this->isClosed = $isClosed;
	}

	/**
	 * 异常处理句柄
	 * 
	 * @param Exception $exception
	 */
	public function exceptionHandle($exception) {
		$trace = array();
		$file = $line = '';
		if (Wind::$isDebug) {
			$trace = $exception->getTrace();
			if (@$trace[0]['file'] == '') {
				unset($trace[0]);
				$trace = array_values($trace);
			}
			$file = @$trace[0]['file'];
			$line = @$trace[0]['line'];
		}
		$this->showErrorMessage($exception->getMessage(), $file, $line, $trace, 
			$exception->getCode());
	}

	/**
	 * 错误处理句柄
	 * 
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 */
	public function errorHandle($errno, $errstr, $errfile, $errline) {
		$trace = array();
		if (Wind::$isDebug) {
			$trace = debug_backtrace();
			unset($trace[0]["function"], $trace[0]["args"]);
		}
		$this->showErrorMessage($this->_friendlyErrorType($errno) . ': ' . $errstr, $errfile, 
			$errline, $trace, $errno);
	}

	/**
	 * 错误处理
	 * 
	 * @param string $message
	 * @param string $file 异常文件
	 * @param int $line 错误发生的行
	 * @param array $trace
	 * @param int $errorcode 错误代码
	 * @throws WindFinalException
	 */
	abstract protected function showErrorMessage($message, $file, $line, $trace, $errorcode);

	/**
	 * 错误信息处理方法
	 * 
	 * @param string $file
	 * @param string $line
	 * @param array $trace
	 */
	protected function crash($file, $line, $trace) {
		if ($trace) {
			$msg = '';
			$count = count($trace);
			$padLen = strlen($count);
			foreach ($trace as $key => $call) {
				if (!isset($call['file']) || $call['file'] == '') {
					$call['file'] = '~Internal Location~';
					$call['line'] = 'N/A';
				}
				$traceLine = '#' . str_pad(($count - $key), $padLen, "0", STR_PAD_LEFT) . '  ' . $this->_getCallLine(
					$call);
				$trace[$key] = $traceLine;
			}
		}
		$fileLines = array();
		if (is_file($file)) {
			$currentLine = $line - 1;
			$fileLines = explode("\n", file_get_contents($file, null, null, 0, 10000000));
			$topLine = $currentLine - 5;
			$fileLines = array_slice($fileLines, $topLine > 0 ? $topLine : 0, 10, true);
			if (($count = count($fileLines)) > 0) {
				$padLen = strlen($count);
				foreach ($fileLines as $line => &$fileLine)
					$fileLine = " " . htmlspecialchars(
						str_pad($line + 1, $padLen, "0", STR_PAD_LEFT) . ": " . str_replace("\t", 
							"    ", rtrim($fileLine)), null, "UTF-8");
			}
		}
		return array($fileLines, $trace);
	}

	/**
	 *
	 * @param array $call
	 * @return string
	 */
	private function _getCallLine($call) {
		$call_signature = "";
		if (isset($call['file'])) $call_signature .= $call['file'] . " ";
		if (isset($call['line'])) $call_signature .= "(" . $call['line'] . ") ";
		if (isset($call['function'])) {
			$call_signature .= $call['function'] . "(";
			if (isset($call['args'])) {
				foreach ($call['args'] as $arg) {
					if (is_string($arg))
						$arg = '"' . (strlen($arg) <= 64 ? $arg : substr($arg, 0, 64) . "…") . '"';
					else if (is_object($arg))
						$arg = "" . get_class($arg) . "";
					else if ($arg === true)
						$arg = "true";
					else if ($arg === false)
						$arg = "false";
					else if ($arg === null)
						$arg = "null";
					else if (is_array($arg))
						$arg = WindString::varToString($arg);
					else
						$arg = strval($arg);
					$call_signature .= $arg . ',';
				}
			}
			$call_signature = trim($call_signature, ',') . ")";
		}
		return $call_signature;
	}

	/**
	 * 返回友好的错误类型名
	 * 
	 * @param int $type
	 * @return string unknown
	 */
	private function _friendlyErrorType($type) {
		switch ($type) {
			case E_ERROR:
				return 'E_ERROR';
			case E_WARNING:
				return 'E_WARNING';
			case E_PARSE:
				return 'E_PARSE';
			case E_NOTICE:
				return 'E_NOTICE';
			case E_CORE_ERROR:
				return 'E_CORE_ERROR';
			case E_CORE_WARNING:
				return 'E_CORE_WARNING';
			case E_CORE_ERROR:
				return 'E_COMPILE_ERROR';
			case E_CORE_WARNING:
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR:
				return 'E_USER_ERROR';
			case E_USER_WARNING:
				return 'E_USER_WARNING';
			case E_USER_NOTICE:
				return 'E_USER_NOTICE';
			case E_STRICT:
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR:
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED:
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED:
				return 'E_USER_DEPRECATED';
		}
		return $type;
	}
}

?>