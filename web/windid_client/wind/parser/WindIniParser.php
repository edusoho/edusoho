<?php
/**
 * ini 格式文件解析
 * 
 * <note><b>注意：</b>有些保留字不能作为 ini 文件中的键名，<br/>
 * 包括：null，yes，no，true 和 false。值为 null，no 和 false 等效于 ""，<br/>
 * 值为 yes 和 true 等效于 "1"。<br/>
 * 字符 {}|&~![()" 也不能用在键名的任何地方，而且这些字符在选项值中有着特殊的意义.
 * </note>
 * true,和false因为会被转义，所以如果希望在解析出的数组中false和true能转变成boolean类型的，则可以给该值加上引号
 * <code>
 * [filters]
 * filter1.isopen='true'
 * filter1.isadd=true
 * </code>
 * 则会解析成：
 * <code>
 * array(
 * 	'filters' => array(
 * 		'isopen' => true,//boolean类型
 * 		'isadd' => '1', //string 类型
 * 	)
 * )
 * </code>
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindIniParser.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package parser
 */
class WindIniParser {

	/**
	 * ini中分割数组的标识
	 * 
	 * @var string
	 */
	const ARRAY_SEP = '.';

	/**
	 * 解析数据
	 * 
	 * @param string $filename ini格式文件
	 * @param boolean $build 是否解析,默认为true
	 * @return boolean
	 */
	public function parse($filename, $build = true) {
		if (!is_file($filename)) return array();
		$data = parse_ini_file($filename, true);
		return $build ? $this->buildData($data) : $data;
	}

	/**
	 * 构建数据
	 * 
	 * @param array $data 将解析出来的数据进行值解析
	 * @return array 解析后的数组
	 */
	private function buildData(&$data) {
		foreach ((array) $data as $key => $value) {
			if (is_array($value)) {
				$data[$key] = $this->formatDataArray($value);
			} else {
				$this->formatDataFromString($key, $value, $data);
			}
		}
		return $data;
	}

	/**
	 * 将每行ini文件转换成数组
	 * 
	 * @param string $key ini文件中的键
	 * @param string $value ini文件中的值
	 * @param array $data 操作数据,默认为array()
	 * @return array
	 */
	private function toArray($key, $value, &$data = array()) {
		if (empty($key)) return array();
		if (strpos($key, self::ARRAY_SEP)) {
			$start = substr($key, 0, strpos($key, self::ARRAY_SEP));
			$end = substr($key, strpos($key, self::ARRAY_SEP) + 1);
			$data[$start] = array();
			$this->toArray($end, $value, $data[$start]);
		} else {
			$__tmp = strtolower($value);
			$data[$key] = 'false' === $__tmp ? false : ('true' === $__tmp ? true : $value);
		}
		return $data;
	}

	/**
	 * 解析ini格式文件成数组
	 * 
	 * @param array $original 原始数组
	 * @param array $data 解析后的数组
	 * @return array
	 */
	private function formatDataArray(&$original, &$data = array()) {
		foreach ((array) $original as $key => $value) {
			$tmp = $this->toArray($key, $value);
			foreach ($tmp as $tkey => $tValue) {
				if (is_array($tValue)) {
					(!isset($data[$tkey])) && $data[$tkey] = array();
					$this->formatDataArray($tValue, $data[$tkey]);
				} else {
					$data[$tkey] = $tValue;
				}
			}
		}
		return $data;
	}

	/**
	 * 从字符串中合并数组
	 * 
	 * @param string $key 待合并的键值
	 * @param  string $value 待合并的数据
	 * @param array $data 操作数组
	 * @return array
	 */
	private function formatDataFromString($key, $value, &$data) {
		$tmp = $this->toArray($key, $value);
		if (false === strpos($key, self::ARRAY_SEP)) return $tmp;
		$start = substr($key, 0, strpos($key, self::ARRAY_SEP));
		if ((!isset($data[$start]) || !is_array($data[$start])) && isset($tmp[$start])) {
			$data[$start] = $tmp[$start];
			unset($data[$key]);
			return $data;
		}
		
		foreach ($data as $d_key => $d_value) {
			if (!isset($tmp[$d_key]) || !is_array($tmp[$d_key])) {
				continue;
			}
			foreach ($tmp[$d_key] as $a => $b) {
				$this->merge($a, $b, $data[$start]);
			}
		}
		unset($data[$key]);
		return $data;
	}

	/**
	 * 合并格式化的数组
	 * 
	 * @param string $key 待合并的键值
	 * @param mixed $value 待合并的数据
	 * @param array $data 合并到的数据
	 * @return array
	 */
	private function merge($key, $value, &$data = array()) {
		if (!is_array($value)) {
			$data[$key] = $value;
			return $data;
		}
		$v_key = array_keys($value);
		$c_key = $v_key[0];
		if (is_array($value[$c_key])) {
			$this->merge($c_key, $value[$c_key], $data[$key]);
		} else {
			$data[$key][$c_key] = $value[$c_key];
		}
		return $data;
	}
}