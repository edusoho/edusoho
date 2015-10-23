<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('WIND:mail.WindMail');

/**
 * 发邮件组件
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwMail.php 24044 2013-01-21 05:33:26Z xiaoxia.xuxx $
 * @package Lib:utility.PwMail
 */
class PwMail {
	
	private $_config = '';
	/**
	 * @var WindMail
	 */
	private $_mail;
	
	public function __construct() {
		$config = Wekit::C('email');
		$this->_config = array(
			'mailOpen' => $config['mailOpen'], 
			'mailMethod' => $config['mailMethod'], 
			'host' => $config['mail.host'], 
			'port' => $config['mail.port'], 
			'from' => $config['mail.from'], 
			'auth' => $config['mail.auth'], 
			'user' => $config['mail.user'], 
			'password' => $config['mail.password'],
			'timeout' => 20);//尝试链接超时时间
		$this->_mail = new WindMail();
		$this->_mail->setCharset(Wekit::V('charset'));
		$this->_mail->setDate(date('r', Pw::getTime()));
		$this->_mail->setContentEncode(WindMail::ENCODE_BASE64);
		$this->_mail->setContentType(WindMail::MIME_HTML);
		$this->_mail->setFrom($this->_config['from'], Wekit::C('site', 'info.name'));
	}

	/**
	 * 普通发邮件方法
	 *
	 * @param string $toUser 收件人
	 * @param string $subject 邮件标题
	 * @param string $content 邮件内容
	 * @return bool
	 */
	public function sendMail($toUser, $subject, $content) {
		if (!$this->_config['mailOpen']) return new PwError('ADMIN:email.close');
		$this->_mail->setSubject($subject);
		$this->_mail->setTo($toUser);
		$this->_mail->setBody($content);
		try {
			$rt = $this->_mail->send($this->getMethod(), $this->_config);
			if (false === $rt) {
				return new PwError('ADMIN:email.server.error');
			}
		} catch(Exception $e) {
			$message = $e->getMessage();
			if (strpos($message, 'Initiates a socket connection fail')) {
				$message = 'ADMIN:email.server.config.error';
			} elseif (strpos($message, '[mail.protocol.WindSmtp.checkResponse]')) {
				$message = 'ADMIN:email.server.response.error';
			}
			return new PwError($message);
		}
		return true;
	}

	/**
	 * 根据后台配置获取发邮件方式
	 *
	 * return string
	 */
	private function getMethod() {
		$methodMap = array(1 => 'php', 2 => 'smtp', 3 => 'send');
		return isset($methodMap[$this->_config['mailMethod']]) ? $methodMap[$this->_config['mailMethod']] : 'smtp';
	}
}