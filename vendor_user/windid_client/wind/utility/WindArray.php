<?php
/**
 * 数组工具类
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindArray.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package utility
 */
class WindArray {

	/**
	 * 按指定key合并两个数组
	 * 
	 * @param string key    合并数组的参照值
	 * @param array $array1  要合并数组
	 * @param array $array2  要合并数组
	 * @return array 返回合并的数组
	 */
	public static function mergeArrayWithKey($key, array $array1, array $array2) {
		if (!$key || !$array1 || !$array2) {
			return array();
		}
		$array1 = self::rebuildArrayWithKey($key, $array1);
		$array2 = self::rebuildArrayWithKey($key, $array2);
		$tmp = array();
		foreach ($array1 as $key => $array) {
			if (isset($array2[$key])) {
				$tmp[$key] = array_merge($array, $array2[$key]);
				unset($array2[$key]);
			} else {
				$tmp[$key] = $array;
			}
		}
		return array_merge($tmp, (array) $array2);
	}

	/**
	 * 按指定key合并两个数组
	 * 
	 * @param string key    合并数组的参照值
	 * @param array $array1  要合并数组
	 * @param array $array2  要合并数组
	 * @return array 返回合并的数组
	 */
	public static function filterArrayWithKey($key, array $array1, array $array2) {
		if (!$key || !$array1 || !$array2) {
			return array();
		}
		$array1 = self::rebuildArrayWithKey($key, $array1);
		$array2 = self::rebuildArrayWithKey($key, $array2);
		$tmp = array();
		foreach ($array1 as $key => $array) {
			if (isset($array2[$key])) {
				$tmp[$key] = array_merge($array, $array2[$key]);
			}
		}
		return $tmp;
	}

	/**
	 * 按指定KEY重新生成数组
	 * 
	 * @param string key 	重新生成数组的参照值
	 * @param array  $array 要重新生成的数组
	 * @return array 返回重新生成后的数组
	 */
	public static function rebuildArrayWithKey($key, array $array) {
		if (!$key || !$array) {
			return array();
		}
		$tmp = array();
		foreach ($array as $_array) {
			if (isset($_array[$key])) {
				$tmp[$_array[$key]] = $_array;
			}
		}
		return $tmp;
	}
}