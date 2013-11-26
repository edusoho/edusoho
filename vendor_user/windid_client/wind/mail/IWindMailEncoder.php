<?php
/**
 * 邮件编码,解码接口
 *
 * @author Qiong Wu <papa0924@gmail.com> 2012-1-1
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package wind
 */
interface IWindMailEncoder {

	/**
	 * 编码邮件内容
	 *
	 * @param string $string
	 * @param int $length
	 * @param string $linebreak
	 */
	public function encode($string, $length, $linebreak);

	/**
	 * 解码邮件内容
	 *
	 * @param string $string
	 * @param int $length
	 * @param string $linebreak
	 */
	public function decode($string, $length, $linebreak);

	/**
	 * 编码邮件头
	 *
	 * @param string $string
	 * @param string $charset
	 * @param int $length
	 * @param string $linebreak
	 */
	public function encodeHeader($string, $charset, $length, $linebread);

	/**
	 * 解码邮件头
	 *
	 * @param string $string
	 * @param string $charset
	 * @param int $length
	 * @param string $linebread
	 */
	public function decodeHeader($string, $charset, $length, $linebread);

}

?>