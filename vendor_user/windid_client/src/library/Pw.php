<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('WIND:utility.WindCookie');

/**
 * 工具类库
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: Pw.php 28776 2013-05-23 08:46:10Z jieyin $
 * @package library
 */
class Pw {

	/**
	 * 取得指定名称的cookie值
	 *
	 * @param string $name cookie名称
	 * @param string $pre cookie前缀,默认为null即没有前缀
	 * @return boolean
	 */
	public static function getCookie($name) {
		$pre = Wekit::C('site', 'cookie.pre');
		$pre && $name = $pre . '_' . $name;
		return WindCookie::get($name);
	}

	/**
	 * 设置cookie
	 *
	 * @param string $name cookie名称
	 * @param string $value cookie值,默认为null
	 * @param string|int $expires 过期时间,默认为null即会话cookie,随着会话结束将会销毁
	 * @param string $pre cookie前缀,默认为null即没有前缀
	 * @param boolean $httponly
	 * @return boolean
	 */
	public static function setCookie($name, $value = null, $expires = null, $httponly = false) {
		$path = $domain = null;
		if ('AdminUser' != $name) {
			$path = Wekit::C('site', 'cookie.path');
			$domain = Wekit::C('site', 'cookie.domain');
		}
		$pre = Wekit::C('site', 'cookie.pre');
		$pre && $name = $pre . '_' . $name;
		$expires && $expires += self::getTime();
		return WindCookie::set($name, $value, false, $expires, $path, $domain, false, $httponly);
	}

	/**
	 * 加密方法
	 *
	 * @param string $str
	 * @param string $key
	 * @return string
	 */
	public static function encrypt($str, $key = '') {
		$key || $key = Wekit::C('site', 'hash');
		/* @var $security IWindSecurity */
		$security = Wind::getComponent('security');
		return base64_encode($security->encrypt($str, $key));
	}
	
	/**
	 * 解密方法
	 *
	 * @param string $str
	 * @param string $key
	 * @return string
	 */
	public static function decrypt($str, $key = '') {
		$key || $key = Wekit::C('site', 'hash');
		/* @var $security IWindSecurity */
		$security = Wind::getComponent('security');
		return $security->decrypt(base64_decode($str), $key);
	}

	/**
	 * 密码加密存储
	 *
	 * @param string $pwd
	 * @return string
	 */
	public static function getPwdCode($pwd) {
		return md5($pwd . Wekit::C('site', 'hash'));
	}

	/**
	 * 求取字符串长度
	 *
	 * @param string $string
	 * @return string
	 */
	public static function strlen($string) {
		return WindString::strlen($string, Wekit::V('charset'));
	}

	/**
	 * 字符串截取
	 *
	 * @param string $string
	 * @param int $length
	 * @param int $start
	 * @param bool $dot
	 */
	public static function substrs($string, $length, $start = 0, $dot = true) {
		if (self::strlen($string) <= $length) return $string;
		return WindString::substr($string, $start, $length, Wekit::V('charset'), $dot);
	}
	
	/**
	 * 清理包含WindCode的字符串
	 *
	 * @param string $text
	 * @param bool $stripTags
	 */
	public static function stripWindCode($text,$stripTags = false) {
		$pattern = array();
		if (strpos($text, '[post]') !== false && strpos($text, '[/post]') !== false) {
			$pattern[] = '/\[post\].+?\[\/post\]/is';
		}
		if (strpos($text, '[img]') !== false && strpos($text, '[/img]') !== false) {
			$pattern[] = '/\[img\].+?\[\/img\]/is';
		}
		if (strpos($text, '[hide=') !== false && strpos($text, '[/hide]') !== false) {
			$pattern[] = '/\[hide=.+?\].+?\[\/hide\]/is';
		}
		if (strpos($text, '[sell') !== false && strpos($text, '[/sell]') !== false) {
			$pattern[] = '/\[sell=.+?\].+?\[\/sell\]/is';
		}
		$pattern[] = '/\[[a-zA-Z]+[^]]*?\]/is';
		$pattern[] = '/\[\/[a-zA-Z]*[^]]\]/is';
	
		$text = preg_replace($pattern, '', $text);
		$stripTags && $text = strip_tags($text);
		return $text;
	}

	/**
	 * 将数据用json加密
	 *
	 * @param mixed $value 需要加密的数据
	 * @param string $charset 字符编码
	 * @return string 加密后的数据
	 */
	public static function jsonEncode($value) {
		return WindJson::encode($value, Wekit::V('charset'));
	}

	/**
	 * 将json格式数据解密
	 *
	 * @param string $value 待解密的数据
	 * @param string $charset 解密后字符串编码
	 * @return mixed 解密后的数据
	 */
	public static function jsonDecode($value) {
		return WindJson::decode($value, true, Wekit::V('charset'));
	}
	
	/**
	 * 将数组简易地转换成json格式
	 *
	 * @param array $var
	 * @return string
	 */
	public static function array2str($var) {
		if (empty($var) || !is_array($var)) return '{}';
		$str = '';
		foreach ($var as $k => $v) {
			$str .= "'" . WindSecurity::escapeHTML($k) . "' : " . (is_array($v) ? self::array2str($v) : "'" . WindSecurity::escapeHTML($v) . "'") . ",";
		}
		return '{' . rtrim($str, ',') . '}';
	}
	
	/**
	 * 从数组(A)中找出指定键值的子集
	 *
	 * @param array $var 数组(A)
	 * @param array $vkeys 指定键值
	 * @return array
	 */
	public static function subArray($var, $vkeys) {
		if (!is_array($var) || !is_array($vkeys)) return array();
		$result = array();
		foreach ($vkeys as $key) {
			if (isset($var[$key])) $result[$key] = $var[$key];
		}
		return $result;
	}
	
	/**
	 * 页码转sql
	 *
	 * @param int $page 分页
	 * @param int $perpage 每页显示数
	 * @return array <1.start 2.limit>
	 */
	public static function page2limit($page, $perpage = 10) {
		$limit = intval($perpage);
		$start = max(($page - 1) * $limit, 0);
		return array($start, $limit);
	}

	/**
	 * 将时间字串转化成零时区时间戳返回
	 *
	 * @param string $str 格式良好的时间串
	 * @return int
	 */
	public static function str2time($str) {
		$timestamp = strtotime($str);
		if ($timezone = Wekit::C('site', 'time.timezone')) $timestamp -= $timezone * 3600;
		return $timestamp;
	}

	/**
	 * 时间戳转字符串
	 *
	 * @example Y-m-d H:i:s  2012-12-12 12:12:12
	 * @param int $timestamp 时间戳
	 * @param string $format 时间格式
	 * @param int $sOffset 时间矫正值
	 * @return string
	 */
	public static function time2str($timestamp, $format = 'Y-m-d H:i') {
		if (!$timestamp) return '';
		if ($format == 'auto') return self::_time2cpstr($timestamp);
		if ($timezone = Wekit::C('site', 'time.timezone')) $timestamp += $timezone * 3600;
		return gmdate($format, $timestamp);
	}

	protected static function _time2cpstr($timestamp) {
		$current = self::getTime();
		$decrease = $current - $timestamp;
		if ($decrease < 0) return self::time2str($timestamp);
		if ($decrease < 60) return $decrease . '秒前';
		if ($decrease < 3600) return ceil($decrease / 60) . '分钟前';
		$decrease = self::str2time(self::time2str($current, 'Y-m-d')) - self::str2time(self::time2str($timestamp, 'Y-m-d'));
		if ($decrease == 0) return self::time2str($timestamp, 'H:i');
		if ($decrease == 86400) return '昨天' . self::time2str($timestamp, 'H:i');
		if ($decrease == 172800) return '前天' . self::time2str($timestamp, 'H:i');
		if (self::time2str($timestamp, 'Y') == self::time2str($current, 'Y')) return self::time2str($timestamp, 'm-d H:i');
		return self::time2str($timestamp);
	}

	/**
	 * 获取矫正过的时间戳值
	 *
	 * @return int
	 */
	public static function getTime() {
		return WEKIT_TIMESTAMP;
	}
	
	/**
	 * 获取今日零点时间戳
	 *
	 * @return int
	 */
	public static function getTdtime() {
		return self::str2time(self::time2str(WEKIT_TIMESTAMP, 'Y-m-d'));
	}
	
	/**
	 * 获取图片路径
	 *
	 * @param string $path
	 * @param int $thumb  0:没有缩略图/1：缩略图/2:迷你缩略图
	 * @param bool $isLocal 是否强制使用本地存储 (默认自动选择)
	 * @return string
	 */
	public static function getPath($path, $ifthumb = 0, $isLocal = false) {
		$storage = Wind::getComponent($isLocal ? 'localStorage' : 'storage');
		return $storage->get($path, $ifthumb);
	}
	
	/**
	 * 获取用户头像地址
	 *
	 * @param int $uid
	 * @param string $size <m.中头像 s.小头像>
	 * @return string
	 */
	public static function getAvatar($uid, $size = 'middle') {
		$file = $uid . (in_array($size, array('middle', 'small')) ? '_' . $size : '') . '.jpg';
		return Wekit::C('site', 'avatarUrl') . '/avatar/' . self::getUserDir($uid) . '/' . $file;
	}
	
	/**
	 * 获取用户头像存储目录
	 *
	 * @param int $uid
	 * @return string
	 */
	public static function getUserDir($uid) {
		$uid = sprintf("%09d", $uid);
		return substr($uid, 0, 3) . '/' . substr($uid, 3, 2) . '/' . substr($uid, 5, 2);
	}
	
	/**
	 * 删除附件
	 *
	 * @param string $path 附件相对地址
	 * @param int $ifthumb 缩略图
	 * @param bool $isLocal 是否强制使用本地存储 (默认自动选择)
	 * @return bool
	 */
	public static function deleteAttach($path, $ifthumb = 0, $isLocal = false) {
		$storage = Wind::getComponent($isLocal ? 'localStorage' : 'storage');
		return $storage->delete($path, $ifthumb);
	}
	
	/**
	 * 删除本地文件
	 *
	 * @param string $filename 文件绝对地址
	 * @return bool
	 */
	public static function deleteFile($filename) {
		return WindFile::del(WindSecurity::escapePath($filename, true));
	}

	/**
	 * 返回html checked
	 *
	 * @param boolean $var
	 * @return string
	 */
	public static function ifcheck($var) {
		return $var ? ' checked' : '';
	}

	/**
	 * 返回html selected
	 *
	 * @param boolean $var
	 * @return string
	 */
	public static function isSelected($var) {
		return $var ? ' selected' : '';
	}

	/**
	 * 返回html current
	 *
	 * @param boolean $var
	 * @return string
	 */
	public static function isCurrent($var) {
		return $var ? ' current' : '';
	}

	/**
	 * 编码转换
	 *
	 * @param string $string 内容字符串
	 * @param string $fromEncoding 原编码
	 * @return string
	 */
	public static function convert($string, $toEncoding, $fromEncoding = '') {
		!$fromEncoding && $fromEncoding = Wekit::V('charset');
		return WindConvert::convert($string, $toEncoding, $fromEncoding);
	}

	/**
	 * 检查是否在线
	 *
	 * @param int $time lastvisit
	 * @return bool
	 */
	public static function checkOnline($time) {
		$onlinetime = $pre = Wekit::C('site', 'onlinetime');
		if ($time + $onlinetime * 60 > self::getTime()) {
			return true;
		}
		return false;
	}
	
	/**
	 * 位运算比对
	 *
	 * @param int $status 状态码
	 * @param int $b 比对位置
	 * @param int $len 比对位数
	 * @return int
	 */
	public static function getstatus($status, $b, $len = 1) {
		return $status >> --$b & (1 << $len) - 1;
	}
	
	public static function windid($api) {
		if (defined('WINDID_IS_NOTIFY')) {
			$cls[$api] = PwWindidStd::getInstance($api);
		} else {
			$cls[$api] = WindidApi::api($api);
		}
		return $cls[$api];
	}
	
	/**
	 * 根据指定的KEY收集二维列表中该key的值
	 * 
	 * 如：有二维数组
	 * $a = array(array('uid' => 1, 'username' => 'xxx'), array('uid' => 2, 'username' => 'test'));
	 * var_export(Pw::collectByKey($a, 'uid'));
	 * //输出：
	 * array(1, 2);
	 * 如果有一个元素中该值不存在，则不收集
	 * 
	 * 注：只作用于二维数组
	 * 
	 * @param array $data 待收集的二维列表
	 * @param string $key 需要收集的
	 * @return array
	 */
	public static function collectByKey($data, $key) {
		if (!is_array($data) || !$key || empty($data)) return array();
		$_collect = array();
		foreach ($data as $_item) {
			if (is_array($_item) && isset($_item[$key])) {
				$_collect[] = $_item[$key];
			}
		}
		return $_collect;
	}
	
	/**
	 * 根据指定的key的顺序，排序数据
	 * 
	 * 如：有二维数组
	 * $a = array(
	 * 	1 => array('id' => 1, 'username' => 'test1'), 
	 * 	3 => array('id' => 3, 'username' => 'test3'), 
	 * 	2 => array('id' => 2, 'username' => 'test2'),
	 *  4 => array('username' => 'test4'),
	 *  5 => array('id' => '', 'username' => 'test5'),
	 *  6 => array('id' => 1, 'username' => 'test6'),
	 *);
	 * var_export(Pw::orderByKeys($a, 'id', array(3,2,1)));
	 * //输出如下：
	 * array(
	 *   5 => array('id' => '', 'username' => 'test5'),
	 * 	 3 => array('id' => 3, 'username' => 'test3'),
	 * 	 2 => array('id' => 2, 'username' => 'test2'),
	 * 	 1 => array('id' => 1, 'username' => 'test1'),
	 *   6 => array('id' => 1, 'username' => 'test6'),
	 * );
	 * 如果某一维元素没有设置key则该元素将不会被排序，如果该KEY的值为空，则该元素值将会在排好序的最前端根据原顺序输出
	 * 注：只作用于二维数组
	 *
	 * @param array $data
	 * @param sring $key
	 * @param array $orders
	 * @return array
	 */
	public static function orderByKeys($data, $key, $orders) {
		if (!is_array($data) || !$key || !is_array($orders) || empty($data) || empty($orders)) return array();
		$_newData = $_tmp =  array();
		foreach ($data as $_k => $_v) {
			if (!isset($_v[$key])) continue;
			if (empty($_v[$key])) {
				$_newData[$_k] = $_v;
				continue;
			}
			if (!isset($_tmp[$_v[$key]])) $_tmp[$_v[$key]] = array();
			$_tmp[$_v[$key]][$_k] = $_v;
		}
		foreach ($orders as $_o) {
			if (!isset($_tmp[$_o])) continue;
			foreach ($_tmp[$_o] as $_k => $_v) {
				$_newData[$_k] = $_v;
			}
		}
		return $_newData;
	}
	
	/**
	 * 重写in_array
	 *
	 * @param int|string $value
	 * @param array $array
	 * @return bool
	 */
	public static function inArray($value, $array) {
		return is_array($array) && in_array($value, $array);
	}
	
	/**
	 * 将HTML标签转义后输出字符串
	 *
	 * @param string $str
	 * @return void
	 */
	public static function echoStr($str) {
		echo WindSecurity::escapeHTML($str);
	}
	
	/**
	 * 将HTML标签转义后输出JSON数据
	 *
	 * @param mixed $data
	 * @return void
	 */
	public static function echoJson($data) {
		echo self::jsonEncode(is_array($data) ? WindSecurity::escapeArrayHTML($data) : WindSecurity::escapeHTML($data));
	}
}