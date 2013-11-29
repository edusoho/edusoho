<?php
/**
 * wind加密解密算法接口定义
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-1
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package security
 */
interface IWindSecurity {

	/**
	 * 加密算法实现接口
	 *
	 * @param string $string
	 * @param string $key
	 * @return string 加密后的结果
	 */
	public function encrypt($string, $key);

	/**
	 * 解密算法实现
	 *
	 * @param string $string
	 * @param string $key
	 * @return string 解密后的结果
	 */
	public function decrypt($string, $key);
}

?>