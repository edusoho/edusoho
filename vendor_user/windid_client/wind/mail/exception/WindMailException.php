<?php
/**
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-21
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package mail
 * @subpackage exception
 */
class WindMailException extends WindException {
	
	/* (non-PHPdoc)
	 * @see WindException::messageMapper()
	 */
	protected function messageMapper($code) {
		return '';
	}
}

?>