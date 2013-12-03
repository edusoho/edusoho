<?php
Wind::import('WIND:utility.WindConvert');
/**
 * json格式转换类
 * 
 * 支持json转php类型,以及php类型转json.
 * @author Long.shi <long.shi@adlibaba-inc.com> 2011-10-19
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindJson.php 3859 2012-12-18 09:25:51Z yishuo $
 * @package utility
 */
class WindJson {
	const JSON_SLICE = 1;
	const JSON_IN_STR = 2;
	const JSON_IN_ARR = 4;
	const JSON_IN_OBJ = 8;
	const JSON_IN_CMT = 16;

	/**
	 * 将数据用json加密
	 * 
	 * @param mixed $value 要加密的值
	 * @param string $charset
	 * @return string
	 */
	public static function encode($source, $charset = 'utf-8') {
		switch (gettype($source)) {
			case 'boolean':
				$source = $source ? 'true' : 'false';
				break;
			case 'NULL':
				$source = 'null';
				break;
			case 'integer':
				$source = (int) $source;
				break;
			case 'double':
			case 'float':
				$source = (float) $source;
				break;
			case 'string':
				$source = self::stringToJson($source, $charset);
				break;
			case 'array':
				$source = self::arrayToJson($source, $charset);
				break;
			case 'object':
				$source = self::objectToJson($source, $charset);
				break;
			default:
				break;
		}
		return $source;
	}

	/**
	 * 将json格式数据解密
	 * 
	 * @param string $str
	 * @param boolean $toArray
	 * @param string $charset
	 * @return mixed
	 */
	public static function decode($str, $toArray = true, $charset = 'utf8') {
		$str = self::_reduceString($str);
		$_str = strtolower($str);
		if ('true' == $_str) {
			return true;
		} elseif ('false' == $_str) {
			return false;
		} elseif ('null' == $_str) {
			return null;
		} elseif (is_numeric($str)) {
			return $str;
		} elseif (preg_match('/^("|\').*(\1)$/s', $_str, $matche) && $matche[1] == $matche[2]) {
			$str = self::jsonToString($str);
		} elseif (preg_match('/^\[.*\]$/s', $_str) || preg_match('/^\{.*\}$/s', $_str)) {
			$str = self::complexConvert($str, $toArray);
		}
		return WindConvert::convert($str, $charset, 'utf8');
	}

	/**
	 * 将json格式转成php string类型
	 *
	 * @param string $string json字符串
	 * @return Ambigous <string, unknown>
	 */
	protected static function jsonToString($string) {
		$delim = substr($string, 0, 1);
		$chrs = substr($string, 1, -1);
		$decodeStr = '';
		for ($c = 0, $length = strlen($chrs); $c < $length; ++$c) {
			$compare = substr($chrs, $c, 2);
			$ordCode = ord($chrs{$c});
			if ('\b' == $compare) {
				$decodeStr .= chr(0x08);
				++$c;
			} elseif ('\t' == $compare) {
				$decodeStr .= chr(0x09);
				++$c;
			} elseif ('\n' == $compare) {
				$decodeStr .= chr(0x0A);
				++$c;
			} elseif ('\f' == $compare) {
				$decodeStr .= chr(0x0C);
				++$c;
			} elseif ('\r' == $compare) {
				$decodeStr .= chr(0x0D);
				++$c;
			} elseif (in_array($compare, array('\\"', '\\\'', '\\\\', '\\/'))) {
				if (('"' == $delim && '\\\'' != $compare) || ("'" == $delim && '\\"' != $compare)) {
					$decodeStr .= $chrs{++$c};
				}
			} elseif (preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6))) {
				$utf16 = chr(hexdec(substr($chrs, ($c + 2), 2))) . chr(hexdec(substr($chrs, ($c + 4), 2)));
				$decodeStr .= WindConvert::utf16beToUTF8($utf16); //self::utf16beToUTF8($utf16);
				$c += 5;
			} elseif (0x20 <= $ordCode && 0x7F >= $ordCode) {
				$decodeStr .= $chrs{$c};
			} elseif (0xC0 == ($ordCode & 0xE0)) {
				$decodeStr .= substr($chrs, $c, 2);
				++$c;
			} elseif (0xE0 == ($ordCode & 0xF0)) {
				$decodeStr .= substr($chrs, $c, 3);
				$c += 2;
			} elseif (0xF0 == ($ordCode & 0xF8)) {
				$decodeStr .= substr($chrs, $c, 4);
				$c += 3;
			} elseif (0xF8 == ($ordCode & 0xFC)) {
				$decodeStr .= substr($chrs, $c, 5);
				$c += 4;
			} elseif (0xFC == ($ordCode & 0xFE)) {
				$decodeStr .= substr($chrs, $c, 6);
				$c += 5;
			}
		}
		return $decodeStr;
	}

	/**
	 * 复杂的json格式转换,支持object array格式
	 * 
	 * @param string $str
	 * @param boolean $toArray
	 * @return Ambigous <multitype:, stdClass>|multitype:|Ambigous <mixed, boolean, NULL, number, multitype:, stdClass, Ambigous, string, unknown>|boolean
	 */
	protected static function complexConvert($str, $toArray = true) {
		if ('[' == $str{0}) {
			$stk = array(self::JSON_IN_ARR);
			$arr = array();
		} else {
			$obj = $toArray ? array() : new stdClass();
			$stk = array(self::JSON_IN_OBJ);
		}
		array_push($stk, array('what' => self::JSON_SLICE, 'where' => 0, 'delim' => false));
		$chrs = substr($str, 1, -1);
		$chrs = self::_reduceString($chrs);
		if ('' == $chrs) {
			return self::JSON_IN_ARR == reset($stk) ? $arr : $obj;
		}
		for ($c = 0, $length = strlen($chrs); $c <= $length; ++$c) {
			$top = end($stk);
			$substr_chrs_c_2 = substr($chrs, $c, 2);
			if (($c == $length) || (($chrs{$c} == ',') && ($top['what'] == self::JSON_SLICE))) {
				$slice = substr($chrs, $top['where'], ($c - $top['where']));
				array_push($stk, array('what' => self::JSON_SLICE, 'where' => ($c + 1), 'delim' => false));
				if (reset($stk) == self::JSON_IN_ARR) {
					array_push($arr, self::decode($slice, $toArray));
				} elseif (reset($stk) == self::JSON_IN_OBJ) {
					if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
						$key = self::decode($parts[1], $toArray);
						$toArray ? $obj[$key] = self::decode($parts[2], $toArray) : $obj->$key = self::decode($parts[2], 
							$toArray);
					} elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
						$toArray ? $obj[$parts[1]] = self::decode($parts[2], $toArray) : $obj->$parts[1] = self::decode(
							$parts[2], $toArray);
					}
				}
			} elseif ((($chrs{$c} == '"') || ($chrs{$c} == "'")) && ($top['what'] != self::JSON_IN_STR)) {
				array_push($stk, array('what' => self::JSON_IN_STR, 'where' => $c, 'delim' => $chrs{$c}));
			} elseif (($chrs{$c} == $top['delim']) && ($top['what'] == self::JSON_IN_STR) && (($chrs{$c - 1} != "\\") || ($chrs{$c - 1} == "\\" && $chrs{$c - 2} == "\\"))) {
				array_pop($stk);
			} elseif (($chrs{$c} == '[') && in_array($top['what'], 
				array(self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ))) {
				array_push($stk, array('what' => self::JSON_IN_ARR, 'where' => $c, 'delim' => false));
			} elseif (($chrs{$c} == ']') && ($top['what'] == self::JSON_IN_ARR)) {
				array_pop($stk);
			} elseif (($chrs{$c} == '{') && in_array($top['what'], 
				array(self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ))) {
				array_push($stk, array('what' => self::JSON_IN_OBJ, 'where' => $c, 'delim' => false));
			} elseif (($chrs{$c} == '}') && ($top['what'] == self::JSON_IN_OBJ)) {
				array_pop($stk);
			} elseif (($substr_chrs_c_2 == '/*') && in_array($top['what'], 
				array(self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ))) {
				array_push($stk, array('what' => self::JSON_IN_CMT, 'where' => ++$c, 'delim' => false));
			} elseif (($substr_chrs_c_2 == '*/') && ($top['what'] == self::JSON_IN_CMT)) {
				array_pop($stk);
				for ($i = $top['where']; $i <= ++$c; ++$i) {
					$chrs = substr_replace($chrs, ' ', $i, 1);
				}
			}
		}
		if (self::JSON_IN_ARR == reset($stk)) {
			return $arr;
		} elseif (self::JSON_IN_OBJ == reset($stk)) {
			return $obj;
		}
		return false;
	}

	/**
	 * 将字符串转化成json格式对象
	 * 
	 * @param string $string
	 * @param string $charset
	 * @return string
	 */
	protected static function stringToJson($string, $charset = 'utf-8') {
		$string = WindConvert::convert($string, 'utf-8', $charset);
		$ascii = '';
		$strlen = strlen($string);
		for ($c = 0; $c < $strlen; ++$c) {
			$b = $string{$c};
			$ordVar = ord($string{$c});
			if (0x08 == $ordVar) {
				$ascii .= '\b';
			} elseif (0x09 == $ordVar) {
				$ascii .= '\t';
			} elseif (0x0A == $ordVar) {
				$ascii .= '\n';
			} elseif (0x0C == $ordVar) {
				$ascii .= '\f';
			} elseif (0x0D == $ordVar) {
				$ascii .= '\r';
			} elseif (in_array($ordVar, array(0x22, 0x2F, 0x5C))) {
				$ascii .= '\\' . $string{$c};
			} elseif (0x20 <= $ordVar && 0x7F >= $ordVar) {
				$ascii .= $string{$c}; //ASCII
			} elseif (0xC0 == ($ordVar & 0xE0)) {
				$char = pack('C*', $ordVar, ord($string{++$c}));
				$ascii .= sprintf('\u%04s', bin2hex(WindConvert::utf8ToUTF16BE($char)));
			} elseif (0xE0 == ($ordVar & 0xF0)) {
				$char = pack('C*', $ordVar, ord($string{++$c}), ord($string{++$c}));
				$ascii .= sprintf('\u%04s', bin2hex(WindConvert::utf8ToUTF16BE($char)));
			} elseif (0xF0 == ($ordVar & 0xF8)) {
				$char = pack('C*', $ordVar, ord($string{++$c}), ord($string{++$c}), ord($string{++$c}));
				$ascii .= sprintf('\u%04s', bin2hex(WindConvert::utf8ToUTF16BE($char)));
			} elseif (0xF8 == ($ordVar & 0xFC)) {
				$char = pack('C*', $ordVar, ord($string{++$c}), ord($string{++$c}), ord($string{++$c}), 
					ord($string{++$c}));
				$ascii .= sprintf('\u%04s', bin2hex(WindConvert::utf8ToUTF16BE($char)));
			} elseif (0xFC == ($ordVar & 0xFE)) {
				$char = pack('C*', $ordVar, ord($string{++$c}), ord($string{++$c}), ord($string{++$c}), 
					ord($string{++$c}), ord($string{++$c}));
				$ascii .= sprintf('\u%04s', bin2hex(WindConvert::utf8ToUTF16BE($char)));
			}
		}
		return '"' . $ascii . '"';
	}

	/**
	 * 将数组转化成json格式对象
	 * 
	 * @param array $array
	 * @param string $charset
	 * @return string
	 */
	protected static function arrayToJson(array $array, $charset = 'utf-8') {
		if (is_array($array) && count($array) && (array_keys($array) !== range(0, sizeof($array) - 1))) {
			array_walk($array, array('WindJson', '_nameValue'), $charset);
			return '{' . join(',', $array) . '}';
		}
		array_walk($array, array('WindJson', '_value'), $charset);
		return '[' . join(',', $array) . ']';
	}

	/**
	 * 将对象转化成json格式对象
	 * 
	 * @param string $object
	 * @param string $charset
	 * @return string
	 */
	protected static function objectToJson($object, $charset = 'utf-8') {
		if ($object instanceof Traversable) {
			$vars = array();
			foreach ($object as $k => $v) {
				$vars[$k] = $v;
			}
		} else {
			$vars = get_object_vars($object);
		}
		array_walk($vars, array('WindJson', '_nameValue'), $charset);
		return '{' . join(',', $vars) . '}';
	}

	/**
	 * @param string $str
	 * @return string
	 */
	private static function _reduceString($str) {
		return trim(preg_replace(array('#^\s*//(.+)$#m', '#^\s*/\*(.+)\*/#Us', '#/\*(.+)\*/\s*$#Us'), '', $str));
	}

	/**
	 * callback函数，用于数组或对象加密
	 *
	 * @param mixed $name
	 * @param mixed $value
	 * @param string $charset
	 */
	private static function _nameValue(&$value, $name, $charset) {
		$value = self::encode(strval($name), $charset) . ':' . self::encode($value, $charset);
	}
	
	/**
	 * callback函数，用于数组加密(无key)
	 *
	 * @param mixed $value
	 * @param mixed $name
	 * @param string $charset
	 */
	private static function _value(&$value, $name, $charset) {
		$value = self::encode($value, $charset);
	}
}

?>