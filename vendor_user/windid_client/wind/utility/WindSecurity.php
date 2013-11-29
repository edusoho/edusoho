<?php
Wind::import('WIND:utility.WindJson');

/**
 * 字符、路径过滤等安全处理
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindSecurity.php 3939 2013-05-29 06:22:57Z xiaoxia.xuxx $
 * @package utility
 */
class WindSecurity {

	/**
	 * 输出json到页面
	 * 添加转义
	 *
	 * @param mixed $source
	 * @param string $charset
	 * @return string
	 */
	public static function escapeEncodeJson($source, $charset = 'utf-8') {
		return WindJson::encode(is_string($source) ? self::escapeHTML($source) : self::escapeArrayHTML($source), $charset);
	}

	/**
	 * 转义输出字符串
	 * 
	 * @param string $str 被转义的字符串
	 * @return string
	 */
	public static function escapeHTML($str, $charset = 'ISO-8859-1') {
		if (!is_string($str)) return $str;
		return htmlspecialchars($str, ENT_QUOTES, $charset);
	}

	/**
	 * 转义字符串
	 * 
	 * @param array $array 被转移的数组
	 * @return array
	 */
	public static function escapeArrayHTML($array) {
		if (!is_array($array)) return self::escapeHTML($array);
		$_tmp = array();
		foreach ($array as $key => $value) {
			is_string($key) && $key = self::escapeHTML($key);
			$_tmp[$key] = is_array($value) ? self::escapeArrayHTML($value) : self::escapeHTML($value);
		}
		return $_tmp;
	}

	/**
	 * 字符串加密
	 * 
	 * @param string $str 需要加密的字符串
	 * @param string $key 密钥
	 * @return string 加密后的结果
	 */
	public static function encrypt($str, $key, $iv = '') {
		if (!$key || !is_string($key)) throw new WindException(
			'[utility.WindSecurity.encrypt] security key is required. ', WindException::ERROR_PARAMETER_TYPE_ERROR);
		if (!$str || !is_string($str)) throw new WindException(
			'[utility.WindSecurity.encrypt] security string is required.', WindException::ERROR_PARAMETER_TYPE_ERROR);
		
		$size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
		$iv = substr(md5($iv ? $iv : $key), -$size);
		$pad = $size - (strlen($str) % $size);
		$str .= str_repeat(chr($pad), $pad);
		@$data = mcrypt_cbc(MCRYPT_DES, $key, $str, MCRYPT_ENCRYPT, $iv);
		return base64_encode($data);
	}

	/**
	 * 解密字符串
	 * 
	 * @param string $str 解密的字符串
	 * @param string $key 密钥
	 * @return string 解密后的结果
	 */
	public static function decrypt($str, $key, $iv = '') {
		if (!$str || !is_string($str)) throw new WindException(
			'[utility.WindSecurity.decrypt] security string is required.', WindException::ERROR_PARAMETER_TYPE_ERROR);
		if (!$key || !is_string($key)) throw new WindException(
			'[utility.WindSecurity.decrypt] security key is required.', WindException::ERROR_PARAMETER_TYPE_ERROR);
		
		$size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
		$iv = substr(md5($iv ? $iv : $key), -$size);
		$str = base64_decode($str);
		@$str = mcrypt_cbc(MCRYPT_DES, $key, $str, MCRYPT_DECRYPT, $iv);
		$pad = ord($str{strlen($str) - 1});
		if ($pad > strlen($str)) return false;
		if (strspn($str, chr($pad), strlen($str) - $pad) != $pad) return false;
		return substr($str, 0, -1 * $pad);
	}

	/**
	 * 创建token令牌串
	 * 创建token令牌串,用于避免表单重复提交等.
	 * 使用当前的sessionID以及当前时间戳,生成唯一一串令牌串,并返回.
	 * 
	 * @deprecated
	 *
	 * @return string
	 */
	public static function createToken() {
		return self::generateGUID();
	}

	/**
	 * 获取唯一标识符串,标识符串的长度为16个字节,128位.
	 * 根据当前时间与sessionID,混合生成一个唯一的串.
	 * 
	 * @return string GUID串,16个字节
	 */
	public static function generateGUID() {
		return substr(md5(WindUtility::generateRandStr(8) . microtime()), -16);
	}

	/**
	 * 路径检查转义
	 * 
	 * @param string $fileName 被检查的路径
	 * @param boolean $ifCheck 是否需要检查文件名，默认为false
	 * @return string
	 */
	public static function escapePath($filePath, $ifCheck = false) {
		$_tmp = array("'" => '', '#' => '', '=' => '', '`' => '', '$' => '', '%' => '', '&' => '', ';' => '');
		$_tmp['://'] = $_tmp["\0"] = '';
		$ifCheck && $_tmp['..'] = '';
		if (strtr($filePath, $_tmp) == $filePath) return preg_replace('/[\/\\\]{1,}/i', '/', $filePath);
		throw new WindException('[utility.WindSecurity.escapePath] file path is illegal');
	}
}