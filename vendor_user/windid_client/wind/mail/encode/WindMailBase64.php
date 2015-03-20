<?php
Wind::import("WIND:mail.IWindMailEncoder");
/**
 * 完成邮件传输过程中Base64的编码和解码
 * 
 * Base 64 是一种通用的方法，其原理很简单，就是把三个Byte的数据用 4 个Byte表示，
 * 这样，这四个Byte 中，实际用到的都只有前面6 bit，这样就不存在只能传输 7bit 的字符的问题了。
 * Base 64的缩写一般是“B”，像这封信中的Subject 就是用的 Base64 编码.
 * @author Qiong Wu <papa0924@gmail.com> 2012-1-1
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package mail
 * @subpackage encode
 */
class WindMailBase64 implements IWindMailEncoder {

	/* (non-PHPdoc)
	 * @see IWindMailEncoder::decode()
	 */
	public function decode($string, $length, $linebreak) {}

	/* (non-PHPdoc)
	 * @see IWindMailEncoder::decodeHeader()
	 */
	public function decodeHeader($string, $charset, $length, $linebread) {}

	/**
	 * 用Base64方式编码邮件内容
	 *
	 * @param string $string
	 * @param int $length
	 * @param string $linebreak
	 */
	public function encode($string, $length, $linebreak) {
		return trim(chunk_split(base64_encode($string), $length, $linebreak));
	}

	/**
	 * 用Base64方式编码邮件头
	 *
	 * @param string $string
	 * @param int $length
	 * @param string $linebreak
	 */
	public function encodeHeader($string, $charset, $length, $linebreak) {
		$prefix = '=?' . $charset . '?B?';
		$suffix = '?=';
		$length = $length - strlen($prefix) - strlen($suffix);
		$string = $this->encode($string, $length, $linebreak);
		return $prefix . strtr($string, array($linebreak => $suffix . $linebreak . " $prefix")) . $suffix;
	}
}

?>