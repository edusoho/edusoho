<?php
/**
 * @author Qiong Wu <papa0924@gmail.com> 2011-11-16
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package http
 * @subpackage mime
 */
class WindMimeType {
	/**
	 * @var array
	 */
	protected static $mimes = null;

	/**
	 * 根据内容的类型返回mime类型
	 *
	 * @param string $type
	 * @return array
	 */
	public static function getMime($type) {
		if (self::$mimes === null) {
			self::$mimes = @include (Wind::getRealPath('WIND:http.mime.mime', false));
		}
		return isset(self::$mimes[$type]) ? self::$mimes[$type] : '';
	}

	/**
	 * 根据请求的mime类型获得返回内容类型
	 *
	 * @param string $mime mime类型
	 * @return string
	 */
	public static function getType($mime) {
		if (self::$mimes === null) {
			self::$mimes = @include (Wind::getRealPath('WIND:http.mime.mime.php', true));
		}
		return array_search($mime, self::$mimes);
	}

}

?>