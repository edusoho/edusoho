<?php
Wind::import('WIND:mail.sender.IWindSendMail');
Wind::import('WIND:mail.protocol.WindSmtp');
/**
 * 邮件发送
 * 
 * 配置信息:<pre>
 * $config = array(
 * 'host' => '',	主机
 * 'port' => '',	端口号
 * 'name' => '',	
 * 'auth' => '',	
 * 'user' => '',
 * 'password' => '',
 * 'timeout' => '', 超时时间,默认60秒
 * )
 * </pre>
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindSmtpMail.php 3298 2012-01-06 12:48:26Z yishuo $ 
 * @package mail
 * @subpackage sender
 */
class WindSmtpMail extends WindModule implements IWindSendMail {
	/**
	 * @var WindSmtp 邮件发送服务器
	 */
	protected $smtp = null;
	/**
	 * 邮件发送服务器
	 *
	 * @var string
	 */
	protected $host;
	/**
	 * 邮件发送端口
	 *
	 * @var int
	 */
	protected $port;
	/**
	 * @var string 邮件主机名
	 */
	protected $name;
	/**
	 * 是否启用验证
	 * 
	 * @var boolean
	 */
	protected $auth;
	/**
	 * @var string 邮件用户名
	 */
	protected $username;
	/**
	 * @var string 邮件密码
	 */
	protected $password;
	/**
	 * 请求超时时间
	 *
	 * @var string
	 */
	protected $timeout = 60;

	/**
	 * @param WindMail $mail 
	 * @see IWindSendMail::send()
	 */
	public function send($mail, $config = array()) {
		if ($this->smtp === null) {
			$this->_init($config);
			$this->smtp = new WindSmtp($this->host, $this->port, $this->timeout);
		}
		$this->smtp->open();
		$this->smtp->ehlo($this->name);
		if ($this->auth) $this->smtp->authLogin($this->username, $this->password);
		$this->smtp->mailFrom($mail->getFrom());
		foreach ($mail->getRecipients() as $rcpt)
			$this->smtp->rcptTo($rcpt);
		$this->smtp->data($mail->createHeader() . $mail->createBody());
		$this->smtp->quit();
		return true;
	}

	/**
	 * @param array() $config
	 */
	private function _init($config) {
		parent::setConfig($config);
		$this->host = $this->getConfig('host', '', '127.0.0.1');
		$this->port = $this->getConfig('port', '', '25');
		$this->name = $this->getConfig('name', '', 'localhost');
		$this->auth = $this->getConfig('auth', '', true);
		$this->username = $this->getConfig('user');
		$this->password = $this->getConfig('password');
		$this->timeout = $this->getConfig('timeout', '', 60);
	}
}