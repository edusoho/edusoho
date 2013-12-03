<?php
Wind::import('WIND:mail.protocol.WindSocket');
/**
 * imap协议封装
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindImap.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package mail
 * @subpackage protocol
 */
class WindImap {

	const CRLF = "\r\n";

	/**
	 * @var string w命令标签
	 */
	const TAG = 'Tag';

	/*--------imap中邮件标记---------*/
	
	/**
	 * @var string 已被阅读
	 */
	const SEEN = '\seen';

	/**
	 * @var string 已被回复
	 */
	const ANSWERED = '\Answered';

	/**
	 * @var string 标识为紧急
	 */
	const FLAGGED = '\Flagged';

	/**
	 * @var string 标识为已删除
	 */
	const DELETED = '\Deleted';

	/**
	 * @var string   草稿
	 */
	const DRAFT = '\Draft';

	/**
	 * @var string 新邮件
	 */
	const RECENT = '\Recent';

	/*--------imap中邮件标记--------*/
	
	/*---------imap中邮件标记类型(store/stripstore方法flags参数)--------*/
	/**
	 * @var string 邮件的一组标志
	 */
	const FLAGS = 'FLAGS';

	/**
	 * @var string 表示一组邮件的标志
	 */
	const SLIENT = 'FLAGS.SLIENT';

	/*---------imap中邮件标记类型(store/stripstore方法flags参数)--------*/
	
	/*--------fetch函数中参数$dataname的值---------*/
	/**
	 * @var string 按照一定格式的邮件摘要，包括邮件标志、RFC822.SIZE、自身的时间和信封信息。
	 */
	const ALL = 'ALL';

	/**
	 * @var string 返回邮件体文本格式和大小的摘要信息
	 */
	const BODY = 'BODY';

	/**
	 * @var string 返回邮件的一些摘要，包括邮件标志、RFC822.SIZE、和自身的时间
	 */
	const FAST = 'FAST';

	/**
	 * @var string 要信息，包括邮件标志、RFC822.SIZE、自身的时间和BODYSTRUCTURE的信息。
	 */
	const FULL = 'FULL';

	/**
	 * @var string 此邮件的标志
	 */
	const FLAG = 'FLAGS';

	/**
	 * @var string  邮件的[MIME-IMB]的体结构。
	 */
	const BODYSTRUCTUR = 'BODYSTRUCTUR';

	/**
	 * @var string 自身的时间。
	 */
	const INTERNALDATE = 'INTERNALDATE';

	/**
	 * @var string 等同于BODY[]。
	 */
	const RFC822 = 'RFC822';

	/**
	 * @var string 邮件的[RFC-2822]大小
	 */
	const RFC822SIZE = 'RFC822.SIZE';

	/**
	 * @var string 等同于BODY.PEEK[HEADER]，
	 */
	const RFC822HEADER = 'RFC822.HEADER';

	/**
	 * @var string 功能上等同于BODY[TEXT]
	 */
	const RFC822TEXT = 'RFC822.TEXT';

	/**
	 * @var string 返回邮件的UID号，UID号是唯一标识邮件的一个号码。
	 */
	const UID = 'UID';

	/*--------fetch函数中参数$dataname的值---------*/
	
	/*--------header中的field--------*/
	/**
	 * @var string 日期
	 */
	const DATE = 'Date';

	/**
	 * @var string 发件人
	 */
	const FROM = 'From';

	/**
	 * @var string 收件人
	 */
	const TO = 'To';

	/**
	 * @var string 抄送地址
	 */
	const CC = 'Cc';

	/**
	 * @var string 抄送地址
	 */
	const BCC = 'Bcc';

	/**
	 * @var string 发送地址
	 */
	const DELIVERED = 'Delivered-To';

	/**
	 * @var string 回复地址
	 */
	const REPLY = 'Reply-To';

	/**
	 * @var string 主题
	 */
	const SUBEJCT = 'Subject';

	/**
	 * @var string MIME内容的类型
	 */
	const CONTENTTYPE = 'Content-Type';

	/**
	 * @var string 内容的传输编码方式 
	 */
	const CONTENTENCODE = 'Content-Transfer-Encoding';

	/**
	 * @var string MIME版本
	 */
	const MIMEVERSION = 'MIME-Version';

	/**
	 * @var string 消息ID
	 */
	const MESSAGEID = 'Message-Id';

	/**
	 * @var string 传输路径
	 */
	const RECEIVED = 'Received';

	/**
	 * @var string 回复地址
	 */
	const RETURNPATH = 'Return-Path';

	/*--------header中的field--------*/
	
	/*--------status命令中所用参数--------*/
	/**
	 * @var string  邮箱中的邮件总数
	 */
	const S_MESSAGES = 'MESSAGES';

	/**
	 * @var string  邮箱中标志为\RECENT的邮件数
	 */
	const S_RECENT = 'RECENT';

	/**
	 * @var string  可以分配给新邮件的下一个UID
	 */
	const S_UIDNEXT = 'UIDNEXT';

	/**
	 * @var string  邮箱的UID有效性标志
	 */
	const S_UIDVALIDITY = 'UIDVALIDITY';

	/**
	 * @var string  邮箱中没有被标志为\UNSEEN的邮件数
	 */
	const S_UNSEEN = 'UNSEEN';

	/*--------status命令中所用参数--------*/
	
	/*--------search命令中所用参数--------*/
	/**
	 * @var string 返回所有的匹配
	 */
	CONST SH_ALL = 'ALL';

	/**
	 * @var string 返回新的邮件
	 */
	CONST SH_NEW = 'NEW';

	/**
	 * @var string 返回邮件中打了\Answered标记的邮件
	 */
	CONST SH_ANSWERED = 'ANSWERED';

	/**
	 * @var string 返回邮件中指字暗送的邮件
	 */
	CONST SH_BCC = 'BCC';

	/**
	 * @var string 返回指定日期已前的邮件
	 */
	CONST SH_BEFORE = 'BEFORE';

	/**
	 * @var string 返回有主体的邮件
	 */
	CONST SH_BODY = 'BODY';

	/**
	 * @var string 返回邮件中打了\Deleted标记的邮件
	 */
	CONST SH_DELETED = 'DELETED';

	/**
	 * @var string 返回邮件中打了\Flagged标记的邮件
	 */
	CONST SH_FLAGGED = 'FLAGGED';

	/**
	 * @var string 返回指定发件人字段的邮件
	 */
	CONST SH_FROM = 'FROM';

	/**
	 * @var string 返回邮件消息中指定keywork的邮件
	 */
	CONST SH_KEYWORD = 'KEYWORD';

	/**
	 * @var string 返回邮件中打了\Recent标记的邮件
	 */
	CONST SH_RECENT = 'RECENT';

	/**
	 * @var string 返回邮件中打了\Seen标记的邮件
	 */
	CONST SH_SEEN = 'SEEN';

	/**
	 * @var string 返回指定日期之后的邮件
	 */
	CONST SH_SINCE = 'SINCE';

	/**
	 * @var string 返回邮件中文本指定字符串的邮件
	 */
	CONST SH_TEXT = 'TEXT';

	/**
	 * @var string 返回指定收件人字段的邮件
	 */
	CONST SH_TO = 'TO';

	/**
	 * @var string 返回邮件中没有打\Answered标记的邮件
	 */
	CONST SH_UNANSWERED = 'UNANSWERED';

	/**
	 * @var string  返回邮件中没有打\Deleted标记的邮件
	 */
	CONST SH_UNDELETED = 'UNDELETED';

	/**
	 * @var string  返回邮件中没有指定关键字的邮件
	 */
	CONST SH_UNKEYWORD = 'UNKEYWORD';

	/**
	 * @var string  返回邮件中没有打\Seen标记的邮件
	 */
	CONST SH_UNSEEN = 'UNSEEN';

	/**
	 * @var string  返回邮件中没有打\UNFLAGGED标记的邮件
	 */
	CONST SH_UNFLAGGED = 'UNFLAGGED';

	/*--------search命令中所用参数--------*/
	
	/******body中的section********/
	const TEXT = 'TEXT';

	const HEADER = 'HEADER';

	/******body中的section********/
	/**
	 * @var WindSocket imap邮件服务器
	 */
	protected $imap = null;

	protected $seperate = ' ';

	protected $request = array();

	protected $resonse = array();

	private $tag = 0;

	public function __construct($host, $port) {
		$this->imap = new WindSocket($host, $port);
	}

	/**
	 * 打开一个imap连接
	 * @return string
	 */
	public function open() {
		$this->imap->open();
		return $this->response('*');
	}

	/**
	 * 登陆
	 * @param string $username
	 * @param string $password
	 * @return string
	 */
	public function login($username, $password) {
		return $this->communicate("LOGIN {$username} {$password}");
	}

	/**
	 * 创建指定名字的新邮箱。邮箱名称通常是带路径的文件夹全名。
	 * @param string $folder;
	 * @param string
	 */
	public function create($folder) {
		return $this->communicate("CREATE {$folder}");
	}

	/**
	 * 除指定名字的文件夹。文件夹名字通常是带路径的文件夹全名，
	 * 当邮箱被删除后，其中的邮件也不复存在。
	 * @param string $folder
	 * @return string
	 */
	public function delete($folder) {
		return $this->communicate("DELETE {$folder}");
	}

	/**
	 * RENAME命令可以修改文件夹的名称，它使用两个参数：当前邮箱名和新邮箱名，
	 * 两个参数的命名符合标准路径命名规则。 
	 * @param string $old 当前邮箱名
	 * @param string $new 新邮箱名，
	 * @return string
	 */
	public function rename($old, $new) {
		return $this->communicate("RENAME {$old} {$new}");
	}

	/**
	 * LIST命令用于列出邮箱中已有的文件夹，有点像操作系统的列目录命令
	 * @param string $base 用户登陆目录
	 * @param string $template 显示的邮箱名。可以使用通配符"*"。
	 * @return string
	 */
	public function folderOfmail($base = '', $template = '*') {
		return $this->communicate("LIST {$base} {$template}");
	}

	/**
	 * 选定某个邮箱（Folder），表示即将对该邮箱（Folder）内的邮件作操作。
	 * 邮箱标志的当前状态也返回给了用户，同时返回的还有一些关于邮件和邮箱的附加信息。
	 * @param string $folder
	 */
	public function select($folder) {
		return $this->communicate("SELECT $folder");
	}

	/**
	 * 读取邮件的文本信息，且仅用于显示的目的。
	 * @param int|string $mail 希望读取的邮件号或者邮冒号分隔的区段
	 * @param string $datanames
	 */
	public function fetch($mail, $datanames = self::ALL) {
		return $this->communicate("FETCH {$mail} {$datanames}");
	}

	/**
	 * 读取邮件的头信息
	 * @param int|string $mail 希望读取的邮件号或者邮冒号分隔的区段
	 * @return string
	 */
	public function fetchHeader($mail) {
		return $this->communicate("FETCH {$mail} BODY[HEADER]");
	}

	/**
	 * 读取邮件的头的字段信息,可能造成不安全，慎用
	 * @param int|string $mail 希望读取的邮件号或者邮冒号分隔的区段
	 * @param string $field 头字段(DATE\SUBJECT\FROM\TO\MESSAGEID\CONTENTTYPE)
	 * @return string
	 */
	public function fetchHeaderFields($mail, $field = self::DATE) {
		$field = is_array($field) ? implode(' ', $field) : $field;
		return $this->communicate("FETCH {$mail} BODY[HEADER.FIELDS ({$field})]");
	}

	/**
	 * 读取邮件的头已排除字段信息
	 * @param int|string $mail 希望读取的邮件号或者邮冒号分隔的区段
	 * @param string $field 头字段(DATE\SUBJECT\FROM\TO\MESSAGEID\CONTENTTYPE)
	 * @return string
	 */
	public function fetchHeaderNotFields($mail, $field = self::DATE) {
		$field = is_array($field) ? implode(' ', $field) : $field;
		return $this->communicate("FETCH {$mail} BODY[HEADER.FIELDS.NOT ({$field})]");
	}

	/**
	 * 读取邮件的MIME
	 * @param int|string $mail 希望读取的邮件号或者邮冒号分隔的区段
	 * @return string
	 */
	public function fetchMime($mail) {
		return $this->communicate("FETCH {$mail} BODY[MIME]");
	}

	/**
	 * 读取邮件的Text
	 * @param int|string $mail 希望读取的邮件号或者邮冒号分隔的区段
	 * @return string
	 */
	public function fetchText($mail) {
		return $this->communicate("FETCH {$mail} BODY[TEXT]");
	}

	/**
	 * 返回邮件的中的某一指定部分，返回的部分用section来表示，
	 * section部分包含的信息通常是代表某一部分的一个数字或者是下面的某一个部分：
	 * HEADER, HEADER.FIELDS, HEADER.FIELDS.NOT, MIME, and TEXT。
	 * 如果section部分是空的话，那就代表返回全部的信息，包括头信息。
	 * @param int|string $mail 希望读取的邮件号或者邮冒号分隔的区段
	 * @param int|string $section 返回的部分
	 * @return string
	 */
	public function fetchBySection($mail, $section = self::TEXT) {
		return $this->communicate("FETCH {$mail} BODY[$section]");
	}

	/**
	 * 返回邮件的中的某一指定部分，返回的部分用section来表示，
	 * section部分包含的信息通常是代表某一部分的一个数字或者是下面的某一个部分：
	 * HEADER, HEADER.FIELDS, HEADER.FIELDS.NOT, MIME, and TEXT。
	 * 如果section部分是空的话，那就代表返回全部的信息，包括头信息。
	 * @param int|string $mail 希望读取的邮件号或者邮冒号分隔的区段
	 * @param int $start 返回的部分的开始
	 * @param int $end   返回的部分的结束
	 * @param int:string $section 返回的部分
	 * @return string
	 */
	public function fetchPartialOfSection($mail, $start, $end, $section = self::TEXT) {
		return $this->communicate("FETCH {$mail} BODY[$section]<{$start}.{$end}>");
	}

	/**
	 * 修改指定邮件的属性，包括给邮件打上已读标记、删除标记等
	 * @param INT|string $mail
	 * @param string $flags imap中的邮件标记,值为SLIENT和FLAGS两种类型
	 * @param STRING|ARRAY $attribute 标记属性(DELETED\ANSWERED\RECENT\DRAFT\FLAGGED)
	 * @return string
	 */
	public function store($mail, $flags = self::FLAGS, $attribute = self::ANSWERED) {
		$attribute = is_array($attribute) ? implode(' ', $attribute) : $attribute;
		return $this->communicate("STORE {$mail} +" . self::FLAGS . " ($attribute)");
	}

	/**
	 * 修改指定邮件的属性，包括给邮件打上已读标记、删除标记等
	 * @param INT|string $mail
	 * @param string $flags imap中的邮件标记,值为SLIENT和FLAGS两种类型
	 * @param STRING|ARRAY $attribute 标记属性(DELETED\ANSWERED\RECENT\DRAFT\FLAGGED)
	 * @return string
	 */
	public function stripStore($mail, $flags = self::FLAGS, $attribute = self::DELETED) {
		$attribute = is_array($attribute) ? implode(' ', $attribute) : $attribute;
		return $this->communicate("STORE {$mail} -" . self::FLAGS . " ($attribute)");
	}

	/**
	 * 结束对当前Folder（文件夹/邮箱）的访问，
	 * 关闭邮箱该邮箱中所有标志为DELETED的邮件就被从物理上删除
	 */
	public function close() {
		return $this->communicate("CLOSE");
	}

	/**
	 * 不关闭邮箱的情况下删除所有的标志为、DELETED的邮件。
	 * EXPUNGE删除的邮件将不可以恢复。 
	 */
	public function expunge() {
		return $this->communicate("EXPUNGE");
	}

	/**
	 * 以只读方式打开邮箱
	 * @param string $mailbox 邮箱
	 * @return string
	 */
	public function examine($mailbox) {
		return $this->communicate("EXAMINE $mailbox");
	}

	/**
	 * 在客户机的活动邮箱列表中增加一个邮箱
	 * @param string $mailbox 希望添加的邮箱名。
	 */
	public function subscribe($mailbox) {
		return $this->communicate("SUBSCRIBE $mailbox");
	}

	/**
	 * 来从活动列表中去掉一个邮箱
	 * @param string $mailbox 希望去掉的邮箱名。
	 */
	public function unsubscribe($mailbox) {
		return $this->communicate("UNSUBSCRIBE $mailbox");
	}

	/**
	 * 修正了LIST命令，LIST返回用户$HOME目录下所有的文件，
	 * 但LSUB命令只显示那些使用SUBSCRIBE命令设置为活动邮箱的文件
	 * @param string $folder  邮箱路径
	 * @param string $mailbox 邮箱名。
	 * @return string
	 */
	public function lsub($folder, $mailbox) {
		return $this->communicate("LSUB {$mailbox} {$mailbox}");
	}

	/**
	 * 查询邮箱的当前状态
	 * @param string $mailbox 需要查询的邮箱名
	 * @param string $params  客户机需要查询的项目列表,S_MESSAGES\S_RECENT\S_UIDNEXT\S_UIDVALIDITY\S_UNSEEN
	 * @return string
	 */
	public function status($mailbox, $params = self::S_MESSAGES) {
		
		$params = is_array($params) ? implode(' ', $params) : $params;
		return $this->communicate("STATUS {$mailbox} ({$params})");
	}

	/**
	 * 在邮箱设置一个检查点,确保内存中的磁盘缓冲数据都被写到了磁盘上。
	 */
	public function check() {
		return $this->communicate("CHECK");
	}

	/**
	 * 根据搜索条件在处于活动状态的邮箱中搜索邮件，然后显示匹配的邮件编号。
	 * @param string $criteria 查询条件参数，明确查询的关键字
	 * @param string $value 查询条件参数，明确查询的关键字的值
	 * @param string $charset 字符集标志,缺省的标志符是US-ASCⅡ
	 * @return string
	 */
	public function search($criteria = self::SH_ALL, $value = null) {
		$search = $criteria;
		if ($value) {
			$search .= ' ' . $value;
		}
		return $this->communicate("SEARCH {$search}");
	}

	/**
	 * UID号是唯一标识邮件系统中邮件的32位证书。
	 * 通常这些命令都使用顺序号来标识邮箱中的邮件，
	 * 使用UID可以使IMAP客户机记住不同IMAP会话中的邮件。
	 */
	public function uid() {
		return $this->communicate("UID");
	}

	/**
	 * 把邮件从一个邮箱复制到另一个邮箱
	 * @param int $soruce 希望从活动邮箱中复制的邮件的标号
	 * @param string $dst 望邮件被复制到的邮箱
	 * @return string
	 */
	public function copy($soruce, $dst) {
		return $this->communicate("COPY {$soruce} {$dst}");
	}

	/**
	 * 返回IMAP服务器支持的功能列表，
	 * 服务器收到客户机发送的CAPABILITY命令后将返回该服务器所支持的功能。
	 */
	public function capability() {
		return $this->communicate("CAPABILITY");
	}

	/**
	 * 结束本次IMAP会话。
	 */
	public function logout() {
		$this->communicate("LOGOUT");
	}

	/**
	 * 发送imap会话请求命令
	 * @param string $request
	 */
	public function request($request) {
		$this->request[] = $request;
		$this->setTag();
		return $this->imap->request($this->getTag() . ' ' . $request . self::CRLF);
	}

	/**
	 * imap会话响应请求
	 * @param int $timeout
	 */
	public function responseLine($timeout = null) {
		if (null !== $timeout) {
			$this->imap->setSocketTimeOut((int) $timeout);
		}
		return $this->imap->responseLine();
	}

	/**
	 * 验证请求
	 * @param boolean $multi
	 * @param int $timeout
	 * @return string
	 */
	public function response($endTag = '*', $timeout = null) {
		$response = '';
		while ('' != ($_response = $this->responseLine($timeout))) {
			list($tag, $status, $info) = explode(' ', $_response, 3);
			if (in_array($status, array('NO', "BAD"))) {
				throw new WindException('[mail.protocol.WindImap.response] ' . $_response);
			}
			$response .= $_response;
			$this->resonse[] = $_response;
			if ($endTag == $tag) {
				break;
			}
		}
		if (empty($response)) throw new WindException('[mail.protocol.WindImap.response] No response');
		return $response;
	}

	/**
	 * 一次imap会号
	 * @param string $request 请求
	 * @param string $response 响应
	 * @return string
	 */
	public function communicate($request, &$response = null) {
		$this->request($request);
		return $response = $this->response($this->getTag());
	}

	/**
	 * 在imap会话中设置新标答
	 */
	public function setTag() {
		$this->tag++;
	}

	/**
	 * 取得imap会号中的标签
	 * @return string
	 */
	public function getTag() {
		return self::TAG . $this->tag;
	}

	public function __destruct() {
		if ($this->imap) {
			$this->logout();
			$this->imap->close();
			$this->imap = null;
		}
	}

}