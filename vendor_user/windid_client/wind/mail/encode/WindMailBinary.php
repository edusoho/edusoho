<?php
Wind::import("WIND:mail.IWindMailEncoder");
/**
 * 二进制编码，消息体内容是没有经过编码的原始数据
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $id$
 * @package mail
 * @subpackage encode
 */
class WindMailBinary implements IWindMailEncoder {
	/* (non-PHPdoc)
	 * @see IWindMailEncoder::encode()
	 */
	public function encode($string, $length, $linebreak) {
		// TODO Auto-generated method stub
		return $string;
	}

	/* (non-PHPdoc)
	 * @see IWindMailEncoder::decode()
	 */
	public function decode($string, $length, $linebreak) {
		// TODO Auto-generated method stub
		
	}

	/* (non-PHPdoc)
	 * @see IWindMailEncoder::encodeHeader()
	 */
	public function encodeHeader($string, $charset, $length, $linebread) {
		// TODO Auto-generated method stub
		return $string;
	}

	/* (non-PHPdoc)
	 * @see IWindMailEncoder::decodeHeader()
	 */
	public function decodeHeader($string, $charset, $length, $linebread) {
		// TODO Auto-generated method stub
		
	}


}

?>