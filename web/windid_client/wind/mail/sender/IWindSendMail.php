<?php
/**
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: IWindSendMail.php 3298 2012-01-06 12:48:26Z yishuo $ 
 * @package mail
 * @subpackage sender
 */
interface IWindSendMail {

	/**
	 * 发送邮件
	 * 
	 * @param WindMail $mail 邮件消息封装对象
	 * @return boolean
	 */
	public function send($mail, $config = array());
}