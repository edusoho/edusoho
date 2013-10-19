<?php
Wind::import('WIND:mail.WindMail');

/**
 * 邮件公共服务
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidMail.php 23833 2013-01-16 06:41:42Z jieyin $
 * @package windid.service.mail
 */
class WindidMail {
	
	private $_defaultConfig = 'WSRV:config.WindidConfig';
	private $_mail;
	private $_config;

	public function __construct($config = null) {
		if (!is_object($config)) {
			$class = Wind::import('WSRV:config.WindidConfig');
			$config = new WindidConfig(WindidConfig::getConfig('mail'));
		}
		$this->_mail = new WindMail();
		$this->_mail->setFrom($config->sendmail);
		
		$this->_config = array('host' => $config->host, 'port' => $config->port, //'name' => 'localhost',
			'auth' => $config->isauth, 'user' => $config->user, 'password' => $config->password);
	}

	/**
	 * 发送邮件
	 *
	 * @param string $toemail 收件人
	 * @param string $subject 邮件标题
	 * @param string $message 邮件内容
	 * @return bool
	 */
	function send($toemail, $subject, $message) {
		//$this->_mail->clearAll();
		$this->_mail->setTo($toemail);
		$this->_mail->setDate();
		$this->_mail->setSubject($subject);
		$this->_mail->setBodyHtml($message);
		$this->_mail->setContentEncode(WindMail::ENCODE_BASE64);
		return $this->_mail->send('smtp', $this->_config);
	}
}