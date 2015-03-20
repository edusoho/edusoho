<?php
/**
 * url帮助类,协助处理url的相关请求
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindUrlHelper.php 3928 2013-01-29 10:21:53Z yishuo $
 * @package web
 */
class WindUrlHelper {

	/**
	 * url检查
	 * 
	 * 当$absolute === true且url不包含协议部分时,默认加上当前应用的协议部分.
	 * @param string $url 需要检查合法性的url
	 * @param boolean $absolute 是否为绝对路径
	 * @return string
	 */
	public static function checkUrl($url, $absolute = true) {
		if ($absolute) {
			$_baseUrl = $absolute === true ? Wind::getComponent('request')->getBaseUrl(true) : $absolute;
			if (strpos($url, '://') === false) {
				$url = trim($_baseUrl, '/') . '/' . trim($url, '/');
			}
		}
		return $url;
	}

	/**
	 * url字符串转化为数组格式
	 * 
	 * 效果同'argsToUrl'相反
	 * @param string $url
	 * @param boolean $decode 是否需要进行url反编码处理
	 * @param string $separator url的分隔符
	 * @return array
	 */
	public static function urlToArgs($url, $decode = true, $separator = '&=') {
		if (strlen($separator) !== 2) return array();
		if (false !== $pos = strpos($url, '?')) $url = substr($url, $pos + 1);
		$url = explode($separator[0], trim($url, $separator[0]));
		$args = array();
		if ($separator[0] === $separator[1]) {
			$_count = count($url);
			for ($i = 0; $i < $_count; $i += 2) {
				if (!isset($url[$i + 1])) {
					$args[] = $decode ? rawurldecode($url[$i]) : $url[$i];
					continue;
				}
				$_k = $decode ? rawurldecode($url[$i]) : $url[$i];
				$_v = $decode ? rawurldecode($url[$i + 1]) : $url[$i + 1];
				$args[$_k] = $_v;
			}
		} else {
			foreach ($url as $value) {
				if (strpos($value, $separator[1]) === false) {
					$args[] = $decode ? rawurldecode($value) : $value;
					continue;
				}
				list($__k, $__v) = explode($separator[1], $value);
				$args[$__k] = $decode && $__v ? rawurldecode($__v) : $__v;
			}
		}
		return $args;
	}

	/**
	 * 将数组格式的参数列表转换为Url格式，并将url进行编码处理
	 * 
	 * <code>参数:array('b'=>'b','c'=>'index','d'=>'d')
	 * 分割符: '&='
	 * 转化结果:&b=b&c=index&d=d
	 * 如果分割符为: '/' 则转化结果为: /b/b/c/index/d/d/</code>
	 * @param array $args
	 * @param boolean $encode 是否进行url编码 默认值为true 
	 * @param string $separator url分隔符 支持双字符,前一个字符用于分割参数对,后一个字符用于分割键值对
	 * @return string
	 */
	public static function argsToUrl($args, $encode = true, $separator = '&=', $key = null) {
		if (strlen($separator) !== 2) return;
		$_tmp = '';
		foreach ((array) $args as $_k => $_v) {
			if ($key !== null) $_k = $key . '[' . $_k . ']';
			if (is_array($_v)) {
				$_tmp .= self::argsToUrl($_v, $encode, $separator, $_k) . $separator[0];
				continue;
			}
			$_v = $encode ? rawurlencode($_v) : $_v;
			if (is_int($_k)) {
				$_v && $_tmp .= $_v . $separator[0];
				continue;
			}
			$_k = ($encode ? rawurlencode($_k) : $_k);
			$_tmp .= $_k . $separator[1] . $_v . $separator[0];
		}
		return trim($_tmp, $separator[0]);
	}

	/**
	 * 解析ControllerPath,并返回解析后的结果集
	 * 
	 * 返回值:array(action,controller,module,args)
	 * <code>action格式:'/module/controller/action/?a=a&b=b&c=c&',前边用斜线分割mca信息,后边用问号分割参数列表.</code>
	 * @param string $controllerPath
	 * @param array $args 默认值为空数组
	 * @return array
	 */
	public static function resolveAction($action, $args = array()) {
		list($action, $_args) = explode('?', $action . '?');
		$args = array_merge($args, ($_args ? self::urlToArgs($_args, false) : array()));
		$action = explode('/', trim($action, '/') . '/');
		end($action);
		return array(prev($action), prev($action), prev($action), $args);
	}

	/**
	 * 构造并返回Url地址
	 * 
	 * 将根据是否开启url重写来分别构造相对应的url
	 * @param string $action 执行的操作
	 * @param array $args 附带的参数
	 * @param string $anchor url锚点
	 * @param AbstractWindRoute $route
	 * @param boolean $absolute 是否返回绝对地址
	 * @return string 返回url地址
	 */
	public static function createUrl($action, $args = array(), $anchor = '', $route = null, $absolute = true) {
		/* @var $router AbstractWindRouter */
		$router = Wind::getComponent('router');
		$url = $router->assemble($action, $args, $route);
		$url .= $anchor ? '#' . $anchor : '';
		return self::checkUrl($url, $absolute);
	}
}
?>