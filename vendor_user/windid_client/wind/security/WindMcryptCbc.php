<?php
Wind::import('WIND:security.IWindSecurity');
/**
 * 基于cbc算法实现的加密组件
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-1
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package security
 */
class WindMcryptCbc implements IWindSecurity {

	/* (non-PHPdoc)
	 * @see IWindSecurity::encrypt()
	 */
	public function encrypt($string, $key, $iv = '') {
		if ($string === '') return '';
		if (!extension_loaded('mcrypt')) {
			throw new WindException('[security.WindMcryptCbc.encrypt] extension \'mcrypt\' is not loaded.');
		}
		if (!$key || !is_string($key)) {
			throw new WindException('[security.WindMcryptCbc.encrypt] security key is required. ', 
				WindException::ERROR_PARAMETER_TYPE_ERROR);
		}
		
		$size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
		$iv = substr(md5($iv ? $iv : $key), -$size);
		$pad = $size - (strlen($string) % $size);
		$string .= str_repeat(chr($pad), $pad);
		return mcrypt_cbc(MCRYPT_DES, $key, $string, MCRYPT_ENCRYPT, $iv);
	}

	/* (non-PHPdoc)
	 * @see IWindSecurity::decrypt()
	 */
	public function decrypt($string, $key, $iv = '') {
		if ($string === '') return '';
		if (!extension_loaded('mcrypt')) {
			throw new WindException('[security.WindMcryptCbc.decrypt] extension \'mcrypt\' is not loaded.');
		}
		if (!$key || !is_string($key)) {
			throw new WindException('[security.WindMcryptCbc.decrypt] security key is required.', 
				WindException::ERROR_PARAMETER_TYPE_ERROR);
		}
		
		$size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
		$iv = substr(md5($iv ? $iv : $key), -$size);
		$str = mcrypt_cbc(MCRYPT_DES, $key, $string, MCRYPT_DECRYPT, $iv);
		$pad = ord($str{strlen($str) - 1});
		if ($pad > strlen($str)) return false;
		if (strspn($str, chr($pad), strlen($str) - $pad) != $pad) return false;
		return substr($str, 0, -1 * $pad);
	}
}

?>