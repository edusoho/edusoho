<?php
Wind::import('WIND:mail.exception.WindMailException');
/**
 * 邮件发送类
 * 
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindMail.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package mail
 */
class WindMail {
	/**
	 *
	 * @var array 邮件头
	 */
	private $mailHeader = array();
	/**
	 *
	 * @var array 邮件附件
	 */
	private $attachment = array();
	/**
	 *
	 * @var string 邮件字符集
	 */
	private $charset = 'utf-8';
	/**
	 *
	 * @var string 是否是内嵌资源
	 */
	private $embed = false;
	/**
	 *
	 * @var array 邮件收件人
	 */
	private $recipients = null;
	/**
	 *
	 * @var string 邮件发件人
	 */
	private $from = '';
	/**
	 *
	 * @var string 邮件消息体html展现方式
	 */
	private $bodyHtml = '';
	/**
	 *
	 * @var string 邮件消息体文本展现方式
	 */
	private $bodyText = '';
	/**
	 *
	 * @var array 邮件边界线
	 */
	private $boundary;
	/**
	 *
	 * @var string 邮件编码方式
	 */
	private $encode = self::ENCODE_BASE64;
	/**
	 *
	 * @var 内容类型
	 */
	private $contentType;
	
	// 常用邮件MIME
	const CRLF = "\n";
	const TO = 'To';
	const CC = 'Cc';
	const BCC = 'Bcc';
	const FROM = 'From';
	const SUBJECT = 'Subject';
	const MESSAGEID = 'Message-Id';
	const CONTENTTYPE = 'Content-Type';
	const CONTENTENCODE = 'Content-Transfer-Encoding';
	const CONTENTID = 'Content-ID';
	const CONTENTPOSITION = 'Content-Disposition';
	const CONTENTDESCRIPT = 'Content-Description';
	const CONTENTLOCATION = 'Content-Location';
	const CONTENTLANGUAGE = 'Content-Language';
	const DATE = 'Date';
	
	// 邮件MIME类型
	const MIME_OCTETSTREAM = 'application/octet-stream';
	const MIME_TEXT = 'text/plain';
	const MIME_HTML = 'text/html';
	const MIME_ALTERNATIVE = 'multipart/alternative';
	const MIME_MIXED = 'multipart/mixed';
	const MIME_RELATED = 'multipart/related';
	
	// 邮件编码
	const ENCODE_7BIT = '7bit';
	const ENCODE_8BIT = '8bit';
	const ENCODE_QP = 'quoted-printable';
	const ENCODE_BASE64 = 'base64';
	const ENCODE_BINARY = 'binary';
	
	// 邮件编码内容
	const DIS_ATTACHMENT = 'attachment';
	const DIS_INLINE = 'inline';
	const LINELENGTH = 72;
	
	// 邮件发送方式
	const SEND_SMTP = 'smtp';
	const SEND_PHP = 'php';
	const SEND_SEND = 'send';

	/**
	 * 发送邮件
	 * 
	 * @param string $type 发送类型
	 * @param array $config 邮件发送器需要的配置数据
	 * @return boolean
	 * @throws Exception
	 */
	public function send($type = self::SEND_SMTP, $config = array()) {
		$class = Wind::import('Wind:mail.sender.Wind' . ucfirst($type) . 'Mail');
		/* @var $sender IWindSendMail */
		$sender = WindFactory::createInstance($class);
		return $sender->send($this, $config);
	}

	/**
	 * 创建邮件头信息
	 * 
	 * @return string
	 */
	public function createHeader() {
		if (!isset($this->mailHeader[self::CONTENTTYPE])) {
			$type = self::MIME_TEXT;
			if ($this->attachment)
				$type = $this->embed ? self::MIME_RELATED : self::MIME_MIXED;
			elseif ($this->bodyHtml)
				$type = $this->bodyText ? self::MIME_ALTERNATIVE : self::MIME_HTML;
			$this->setContentType($type);
		}
		if (!isset($this->mailHeader[self::CONTENTENCODE])) $this->setContentEncode();
		$header = '';
		foreach ($this->mailHeader as $key => $value) {
			if (!$value) continue;
			$header .= $key . ': ';
			if (is_array($value)) {
				foreach ($value as $_key => $_value)
					$header .= (is_string($_key) ? $_key . ' ' . $_value : $_value) . ',';
				$header = trim($header, ',');
			} else
				$header .= $value;
			$header .= self::CRLF;
		}
		return $header . self::CRLF;
	}

	/**
	 * 创建邮件消息体
	 * 
	 * @return string
	 */
	public function createBody() {
		$body = '';
		switch ($this->contentType) {
			case self::MIME_TEXT:
				$body = $this->_encode($this->bodyText) . self::CRLF;
				break;
			case self::MIME_HTML:
				$body = $this->_encode($this->bodyHtml) . self::CRLF;
				break;
			case self::MIME_ALTERNATIVE:
				$body = $this->_createBoundary($this->_boundary(), 'text/plain');
				$body .= $this->_encode($this->bodyText) . self::CRLF;
				$body .= $this->_createBoundary($this->_boundary(), 'text/html');
				$body .= $this->_encode($this->bodyHtml) . self::CRLF;
				$body .= $this->_boundaryEnd($this->_boundary());
				break;
			default:
				$body .= $this->_boundaryStart($this->_boundary());
				$body .= sprintf("Content-Type: %s;%s" . "\tboundary=\"%s\"%s", 
					'multipart/alternative', self::CRLF, $this->_boundary(1), 
					self::CRLF . self::CRLF);
				$body .= $this->_createBoundary($this->_boundary(1), 'text/plain') . self::CRLF;
				$body .= $this->_encode($this->bodyText) . self::CRLF . self::CRLF;
				$body .= $this->_createBoundary($this->_boundary(1), 'text/html') . self::CRLF;
				$body .= $this->_encode($this->bodyHtml) . self::CRLF . self::CRLF;
				$body .= $this->_boundaryEnd($this->_boundary(1));
				$body .= $this->_attach();
				break;
		}
		return $body;
	}

	/**
	 * 设置发件人
	 * 
	 * @param string $email 发件人邮箱
	 * @param string $name 发件人姓名
	 * @return void
	 */
	public function setFrom($email, $name = null) {
		if (!$email || !is_string($email)) return;
		$this->from = $email;
		$name && $email = $this->_encodeHeader($name) . ' <' . $email . '>';
		$this->setMailHeader(self::FROM, $email, false);
	}

	/**
	 * 取得发件人
	 * 
	 * @return string
	 */
	public function getFrom() {
		return $this->from;
	}

	/**
	 * 设置收件人
	 * 
	 * @param string|array $email 收件人邮箱
	 * @param string $name 收件人姓名
	 */
	public function setTo($email, $name = null) {
		if (!$email) return;
		$email = $this->_setRecipientMail($email, $name);
		$this->setMailHeader(self::TO, $email);
	}

	/**
	 * 取得收件人
	 * 
	 * @return array
	 */
	public function getTo() {
		return $this->getMailHeader(self::TO);
	}

	/**
	 * 设置抄送人
	 * 
	 * @param string $email 抄送人邮箱
	 * @param string $name 抄送人姓名
	 */
	public function setCc($email, $name = null) {
		if (!$email) return;
		$email = $this->_setRecipientMail($email, $name);
		$this->setMailHeader(self::CC, $email);
	}

	/**
	 * 取得抄送的对象
	 * 
	 * @return array
	 */
	public function getCc() {
		return $this->getMailHeader(self::CC);
	}

	/**
	 * 设置暗送人
	 * 
	 * @param string $email 暗送人邮箱
	 * @param string $name 暗送人姓名
	 */
	public function setBcc($email, $name = null) {
		if (!$email) return;
		$email = $this->_setRecipientMail($email, $name);
		$this->setMailHeader(self::BCC, $email);
	}

	/**
	 * 取得暗送对象
	 * 
	 * @return array
	 */
	public function getBcc() {
		return $this->getMailHeader(self::BCC);
	}

	/**
	 * 设置邮件主题
	 * 
	 * @param string $subject 主题
	 */
	public function setSubject($subject) {
		$this->setMailHeader(self::SUBJECT, $this->_encodeHeader($subject), false);
	}

	/**
	 * 取得邮件主题
	 * 
	 * @return string
	 */
	public function getSubject() {
		$subject = $this->getMailHeader(self::SUBJECT);
		is_array($subject) && $subject = $subject[0];
		return str_replace(array("\r", "\n"), array('', ' '), $subject);
	}

	/**
	 * 设置邮件日期
	 * 
	 * @param string $data
	 */
	public function setDate($date) {
		$this->setMailHeader(self::DATE, $date);
	}

	/**
	 * 设置邮件头
	 * 
	 * @param string $name 邮件头名称
	 * @param string $value 邮件头对应的值
	 * @param boolean $append 是否是追加
	 * @return void
	 */
	public function setMailHeader($name, $value, $append = true) {
		is_array($value) || $value = array($value);
		if (false === $append || !isset($this->mailHeader[$name])) {
			$this->mailHeader[$name] = $value;
		} else {
			foreach ($value as $key => $_value) {
				if (is_string($key))
					$this->mailHeader[$name][$key] = $_value;
				else
					$this->mailHeader[$name][] = $_value;
			}
		}
	}

	/**
	 * 返回邮件头信息值
	 * 
	 * @param string $name
	 */
	public function getMailHeader($name) {
		if (!$name) return $this->mailHeader;
		return isset($this->mailHeader[$name]) ? $this->mailHeader[$name] : array();
	}

	/**
	 * 设置邮件消息ID
	 */
	public function setMessageId() {
		$user = array_pop($this->getFrom());
		$user || $user = getmypid();
		if ($recipient = $this->getRecipients()) {
			$recipient = array_rand($recipient);
		} else
			$recipient = 'No recipient';
		$host = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : php_uname('n');
		$message = sha1(time() . $user . mt_rand() . $recipient) . '@' . $host;
		$this->setMailHeader(self::MESSAGEID, '<' . $message . '>');
	}

	/**
	 * 设置邮件编码
	 * 
	 * @param string $encode
	 */
	public function setContentEncode($encode = self::ENCODE_BASE64) {
		$this->encode = $encode;
		$this->setMailHeader(self::CONTENTENCODE, $encode);
	}

	/**
	 * 设置邮件类型
	 * 
	 * @param string $type
	 */
	public function setContentType($type = self::MIME_TEXT) {
		if (self::MIME_TEXT == $type || self::MIME_HTML == $type)
			$contentType = sprintf("%s; charset=\"%s\"", $type, $this->charset);
		elseif (self::MIME_RELATED == $type)
			$contentType = sprintf("%s;%s type=\"text/html\";%s boundary=\"%s\"", 
				self::MIME_RELATED, self::CRLF, self::CRLF, $this->_boundary());
		else
			$contentType = sprintf("%s;%s boundary=\"%s\"", $type, self::CRLF, $this->_boundary());
		$this->contentType = $type;
		$this->setMailHeader(self::CONTENTTYPE, $contentType, false);
	}

	/**
	 * 上传附件
	 * 
	 * @return string
	 */
	private function _attach() {
		$attach = '';
		foreach ($this->attachment as $key => $value) {
			list($stream, $mime, $disposition, $encode, $filename, $cid) = $value;
			$filename || $filename = 'attachment_' . $key;
			$attach .= $this->_boundaryStart($this->_boundary());
			$attach .= sprintf(self::CONTENTTYPE . ": %s; name=\"%s\"%s", $mime, $filename, 
				self::CRLF);
			$attach .= sprintf(self::CONTENTENCODE . ": %s%s", $encode, self::CRLF);
			if ($disposition == 'inline') {
				$attach .= sprintf(self::CONTENTID . ": <%s>%s", $cid, self::CRLF);
			}
			$attach .= sprintf(self::CONTENTPOSITION . ": %s; filename=\"%s\"%s%s", $disposition, 
				$filename, self::CRLF, self::CRLF);
			$attach .= $this->_encode($stream, $encode) . self::CRLF;
		}
		$attach .= $this->_boundaryEnd($this->_boundary());
		return $attach;
	}

	/**
	 * 取得下一个quoted-printable
	 * 
	 * @param string $string
	 * @return string
	 */
	private static function getNextQpToken($string) {
		return '=' == substr($string, 0, 1) ? substr($string, 0, 3) : substr($string, 0, 1);
	}

	/**
	 * 获取边界线
	 * 
	 * @return string
	 */
	private function _createBoundary($boundary, $contentType, $charset = '', $encode = '') {
		$result = '';
		$charset || $charset = $this->charset;
		$encode || $encode = $this->encode;
		$result .= $this->_boundaryStart($boundary);
		$result .= sprintf(self::CONTENTTYPE . ": %s; charset=\"%s\"", $contentType, $charset);
		$result .= self::CRLF;
		$result .= sprintf(self::CONTENTENCODE . ": %s%s", $encode, self::CRLF);
		$result .= self::CRLF;
		return $result;
	}

	/**
	 *
	 * @param boundary
	 * @return string
	 */
	private function _boundaryStart($boundary) {
		return '--' . $boundary . self::CRLF;
	}

	/**
	 * 获取结束边界线
	 * 
	 * @return string
	 */
	private function _boundaryEnd($boundary) {
		return self::CRLF . '--' . $boundary . '--' . self::CRLF;
	}

	/**
	 * 设置并返回边界线
	 * 
	 * @param int $i 默认值为0
	 * @return string
	 */
	private function _boundary($i = 0) {
		if (!$this->boundary) {
			$uniq_id = md5(uniqid(time()));
			$this->boundary[0] = 'b1_' . $uniq_id;
			$this->boundary[1] = 'b2_' . $uniq_id;
		}
		return $i == 1 ? $this->boundary[1] : $this->boundary[0];
	}

	/**
	 * 编码邮件内容
	 * 
	 * @param string $message
	 * @param string $encode
	 * @return string
	 */
	private function _encode($message, $encode = '') {
		return $this->_getEncoder($encode)->encode(trim($message), self::LINELENGTH, self::CRLF);
	}

	/**
	 * 编码邮件头部
	 * 
	 * @param string $message
	 * @param string $encode
	 * @return string
	 */
	private function _encodeHeader($message, $encode = '') {
		$message = strtr(trim($message), array("\r" => '', "\n" => '', "\r\n" => ''));
		return $this->_getEncoder($encode)->encodeHeader($message, $this->charset, self::LINELENGTH, 
			self::CRLF);
	}

	/**
	 * 根据当前编码获取邮件编码器，并返回邮件编码器对象
	 * 
	 * @param encode
	 * @return IWindMailEncoder
	 */
	private function _getEncoder($encode) {
		$encode || $encode = $this->encode;
		switch ($encode) {
			case self::ENCODE_QP:
				$mailEncoder = Wind::import("WIND:mail.encode.WindMailQp");
				break;
			case self::ENCODE_BASE64:
				$mailEncoder = Wind::import("WIND:mail.encode.WindMailBase64");
				break;
			case self::ENCODE_7BIT:
			case self::ENCODE_8BIT:
			default:
				$mailEncoder = Wind::import("WIND:mail.encode.WindMailBinary");
				break;
		}
		if (!class_exists($mailEncoder)) throw new WindMailException(
			'[mail.WindMail._encode] encod class for ' . $encode . ' is not exist.');
		return new $mailEncoder();
	}

	/**
	 *
	 * @param string $email
	 * @param string $name
	 */
	private function _setRecipientMail($email, $name) {
		$_email = '';
		if (is_array($email)) {
			foreach ($email as $_e => $_n) {
				$_email .= $_n ? $this->_encodeHeader($_n) . ' <' . $_e . '>' : $_e;
				$this->recipients[] = $_e;
			}
		} else {
			$_email = $name ? $this->_encodeHeader($name) . ' <' . $email . '>' : $email;
			$this->recipients[] = $email;
		}
		return $_email;
	}

	/**
	 * 取得真实的收件人
	 * 
	 * @return array
	 */
	public function getRecipients() {
		return $this->recipients;
	}

	/**
	 * 设置附件
	 * 
	 * @param string $stream 附件名或者附件内容
	 * @param string $mime 附件类型
	 * @param string $disposition 附件展现方式
	 * @param string $encode 附件编码
	 * @param string $filename 文件名
	 * @param string $cid 内容ID
	 */
	public function setAttachment($stream, $mime = self::MIME_OCTETSTREAM, $disposition = self::DIS_ATTACHMENT, $encode = self::ENCODE_BASE64, $filename = null, $cid = 0) {
		$this->attachment[] = array($stream, $mime, $disposition, $encode, $filename, $cid);
	}

	/**
	 * 设置邮件展示内容
	 * 
	 * @param string $body
	 */
	public function setBody($body) {
		$this->bodyHtml = $body;
	}

	/**
	 * 设置邮件文本展示内容
	 * 
	 * @param string $bodyText
	 */
	public function setBodyText($bodyText) {
		$this->bodyText = $bodyText;
	}

	/**
	 * 设置邮件字符
	 * 
	 * @param string $charset
	 */
	public function setCharset($charset) {
		$this->charset = $charset;
	}

	/**
	 * 设置是否是内嵌资源
	 * 
	 * @param boolean $embed
	 */
	public function setEmbed($embed = false) {
		$this->embed = $embed;
	}
}