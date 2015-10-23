<?php
Wind::import('WIND:mail.sender.IWindSendMail');
/**
 * 使用php内部函数发送邮件
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindPhpMail.php 3583 2012-05-28 03:35:10Z yishuo $ 
 * @package mail
 * @subpackage sender
 */
class WindPhpMail implements IWindSendMail {

	/* (non-PHPdoc)
	 * @see IWindSendMail::send()
	 */
	public function send($mail, $config = array()) {
		$to = '';
		foreach ($mail->getRecipients() as $key => $value)
			$to .= $to ? ', ' . $value : $value;
		return mail($to, $mail->getSubject(), $mail->createBody(), $mail->createHeader());
	}
}