<?php
/**
 * properties格式文件解析
 * 
 * properties文件中的注释为单行注释以#标记
 * 同时该文件也允许配置节名称，比如：
 * <code>
 * [test]
 * path=index.php
 * address.zipcode=10000
 * address.show=true //true将会被解析为boolean类型的true，false也将被解析成boolean类型的false
 * </code>
 * 则上述的格式会解析成如下数组:
 * <code>
 * array(
 * 'test' => array(
 * 		'path' => 'index.php',
 * 		'address' => array(
 * 			'zipcode' => '10000',
 * 			'show' => true
 *  	)
 *  )
 *)
 * </code>
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindPropertiesParser.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package parser
 */
class WindPropertiesParser {

	const COMMENT = '#';

	const LPROCESS = '[';

	const RPROCESS = ']';

	const ARRAY_SEP = '.';

	public function __construct() {}

	/**
	 * 解析properties文件里的内容
	 * 
	 * @param string $filename 文件名
	 * @param boolean $build   是否按格式解析数据默认为true
	 * @return array
	 */
	public function parse($filename, $build = true) {
		$data = $this->parse_properties_file($filename);
		return $build ? $this->buildData($data) : $data;
	}

	/**
	 * 解析properties文件并返回一个多维数组
	 * 
	 * 载入一个由 filename 指定的 properties 文件，
	 * 并将其中的设置作为一个联合数组返回。
	 * 
	 * @param string $filename 文件名
	 * @return array
	 */
	private function parse_properties_file($filename) {
		if (!is_file($filename) || !in_array(substr($filename, strrpos($filename, '.') + 1), array('properties'))) {
			return array();
		}
		$content = explode("\n", WindFile::read($filename));
		$data = array();
		$last_process = $current_process = '';
		foreach ($content as $key => $value) {
			$value = str_replace(array("\n", "\r"), '', trim($value));
			if (0 === strpos(trim($value), self::COMMENT) || in_array(trim($value), array('', "\t", "\n"))) {
				continue;
			}
			$tmp = explode('=', $value, 2);
			if (0 === strpos(trim($value), self::LPROCESS) && (strlen($value) - 1) === strrpos($value, self::RPROCESS)) {
				$current_process = $this->trimChar(trim($value), array(self::LPROCESS, self::RPROCESS));
				$data[$current_process] = array();
				$last_process = $current_process;
				continue;
			}
			$tmp[0] = trim($tmp[0]);
			if (count($tmp) == 1 ) {
				$last_process ? $data[$last_process][$tmp[0]] = '' : $data[$tmp[0]] = '';
				continue;
			}
			$tmp[1] = trim($tmp[1], '\'"');
			$__tmpValue = strtolower($tmp[1]);
			$tmp[1] = 'false' === $__tmpValue ? false : ('true' === $__tmpValue ? true : $tmp[1]);
			
			$last_process ? $data[$last_process][$tmp[0]] = $tmp[1] : $data[$tmp[0]] = $tmp[1];
		}
		return $data;
	}

	/**
	 * 解析配置数据
	 * 
	 * @param array $data 源数据
	 * @return array
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
	 * 将每行properties文件转换成数组
	 * 
	 * @param string $key properties文件中的键
	 * @param string $value properties文件中的值
	 * @param array $data 操作数据,默认为array()
	 * @return array
	 */
	private function toArray($key, $value, &$data = array()) {
		if (empty($key) && empty($value)) return array();
		if (strpos($key, self::ARRAY_SEP)) {
			$start = substr($key, 0, strpos($key, self::ARRAY_SEP));
			$end = substr($key, strpos($key, self::ARRAY_SEP) + 1);
			$data[$start] = array();
			$this->toArray($end, $value, $data[$start]);
		} else {
			$data[$key] = $value;
		}
		return $data;
	}

	/**
	 * 将原始数组合并成新的数组
	 * 
	 * @param array $original 原始数组
	 * @param array $data 合并后的数组
	 * @return array
	 */
	private function formatDataArray(&$original, &$data = array()) {
		foreach ((array) $original as $key => $value) {
			$tmp = $this->toArray($key, $value);
			foreach ($tmp as $tkey => $tValue) {
				if (is_array($tValue)) {
					if (!isset($data[$tkey])) {
						$data[$tkey] = array();
					}
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
		if (false == strpos($key, self::ARRAY_SEP))  return $tmp;
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

	/**
	 * 去除字符串头和尾中指定字符
	 * 
	 * @param string $str 待处理的数据
	 * @param mixed $char 需要取出的字符
	 * @return string 处理后的数据
	 */
	private function trimChar($str, $char = ' ') {
		$char = is_array($char) ? $char : array($char);
		foreach ($char as $value) {
			$str = trim($str, $value);
		}
		return $str;
	}
}