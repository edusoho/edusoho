<?php
defined('RUNTIME_START') or define('RUNTIME_START', microtime(true));
defined('USEMEM_START') or define('USEMEM_START', memory_get_usage());
/**
 * 调试工具
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindDebug.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package log
 */
class WindDebug {

	/**
	 * @var array 调试点
	 */
	private static $breakpoint = array();

	/**
	 * @var int 保留的小数位数
	 */
	const DECIMAL_DIGITS = 4;

	/**
	 * @var int 记录内存使用标记
	 */
	const MEMORY = 'mem';

	/**
	 * @var int 记录程序运行时时间使用标记
	 */
	const RUN_TIME = 'time';

	/**
	 * 设置调试点
	 * @param string $point 调试点
	 */
	public static function setBreakPoint($point = '') {
		if (isset(self::$breakpoint[$point])) return false;
		self::$breakpoint[$point][self::RUN_TIME] = microtime(true);
		self::$breakpoint[$point][self::MEMORY] = memory_get_usage();
		return true;
	}

	/**
	 * 移除调试点
	 * @param string $point 调试点
	 */
	public static function removeBreakPoint($point = '') {
		if ($point) {
			if (isset(self::$breakpoint[$point])) unset(self::$breakpoint[$point]);
		} else {
			self::$breakpoint = array();
		}
	}

	/**
	 * 取得系统运行所耗内存
	 */
	public static function getMemUsage() {
		$useMem = memory_get_usage() - USEMEM_START;
		return $useMem ? round($useMem / 1024, self::DECIMAL_DIGITS) : 0;
	}

	/**
	 * 取得系统运行所耗时间
	 */
	public static function getExecTime() {
		$useTime = microtime(true) - RUNTIME_START;
		return $useTime ? round($useTime, self::DECIMAL_DIGITS) : 0;
	}

	/**
	 * 获取调试点
	 * @param $point
	 * @param $label
	 */
	public static function getBreakPoint($point, $label = '') {
		if (!isset(self::$breakpoint[$point])) return array();
		return $label ? self::$breakpoint[$point][$label] : self::$breakpoint[$point];
	}

	/**
	 * 调试点之间系统运行所耗内存
	 * @param string $beginPoint 开始调试点
	 * @param string $endPoint   结束调试点
	 * @return float 
	 */
	public static function getMemUsageOfp2p($beginPoint, $endPoint = '') {
		if (!isset(self::$breakpoint[$beginPoint])) return 0;
		$endMemUsage = isset(self::$breakpoint[$endPoint]) ? self::$breakpoint[$endPoint][self::MEMORY] : memory_get_usage();
		$useMemUsage = $endMemUsage - self::$breakpoint[$beginPoint][self::MEMORY];
		return round($useMemUsage / 1024, self::DECIMAL_DIGITS);
	}

	/**
	 * 调试点之间的系统运行所耗时间
	 * @param string $beginPoint 开始调试点
	 * @param string $endPoint   结束调试点
	 * @return float 
	 */
	public static function getExecTimeOfp2p($beginPoint, $endPoint = '') {
		if (!isset(self::$breakpoint[$beginPoint])) return 0;
		$endTime = self::$breakpoint[$endPoint] ? self::$breakpoint[$endPoint][self::RUN_TIME] : microtime(true);
		$useTime = $endTime - self::$breakpoint[$beginPoint][self::RUN_TIME];
		return round($useTime, self::DECIMAL_DIGITS);
	}

	/**
	 * 堆栈情况
	 * @param array $trace 堆栈引用，如异常
	 * @return array 
	 */
	public static function trace($trace = array()) {
		$debugTrace = $trace ? $trace : debug_backtrace();
		$traceInfo = array();
		foreach ($debugTrace as $info) {
			$info['args'] = self::traceArgs($info['args']);
			$file = isset($info['file']) ? $info['file'] : '';
			$line = isset($info['line']) ? $info['line'] : '';
			$str = '[' . date("Y-m-d H:i:m") . '] ' . $file . ' (line:' . $line . ') ';
			$str .= $info['class'] . $info['type'] . $info['function'] . '(';
			$str .= implode(', ', $info['args']);
			$str .= ")";
			$traceInfo[] = $str;
		}
		return $traceInfo;
	}

	/**
	 * 获取系统所加载的文件
	 */
	public static function loadFiles() {
		return get_included_files();
	}

	public static function debug($message = '', $trace = array(), $begin = '', $end = '') {
		$runtime = self::getExecTime();
		$useMem = self::getMemUsage();
		$separate = "<br/>";
		$trace = implode("{$separate}", self::trace($trace));
		$debug = '';
		$debug .= "{$message}{$separate}";
		$debug .= "Runtime:{$runtime}s{$separate}";
		$debug .= "Memory consumption:{$useMem}byte{$separate}";
		$debug .= "Stack conditions:{$separate}{$trace}{$separate}";
		if ($begin && $end) {
			$PointUseTime = self::getExecTimeOfp2p($begin, $end);
			$PointUseMem = self::getMemUsageOfp2p($begin, $end);
			$debug .= "Between points {$begin} and {$end} debugging system conditions:{$separate}";
			$debug .= "Runtime:{$PointUseTime}s{$separate}";
			$debug .= "Memory consumption:{$PointUseMem}byte{$separate}";
		}
		return $debug;
	}

	private static function traceArgs($args = array()) {
		foreach ($args as $key => $arg) {
			if (is_array($arg))
				$args[$key] = 'array(' . implode(',', $arg) . ')';
			elseif (is_object($arg))
				$args[$key] = 'class ' . get_class($arg);
			else
				$args[$key] = $arg;
		}
		return $args;
	}

}
?>