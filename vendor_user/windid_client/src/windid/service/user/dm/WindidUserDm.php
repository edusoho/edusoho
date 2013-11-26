<?php
Wind::import('WINDID:library.WindidUtility');

/**
 * 用户信息数据模型
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidUserDm.php 24943 2013-02-27 03:52:21Z jieyin $
 * @package windid.service.user.dm
 */
class WindidUserDm extends PwBaseDm {
	
	public $uid;
	public $password = '';
	
	public function __construct($uid = 0) {
		$this->uid = $uid;
	}

	/**
	 * 设置用户名字
	 * 
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->_data['username'] = $username;
		return $this;
	}
	
	/**
	 * 设置用户密码
	 * 
	 * @param string $password 新密码
	 * @return WindidUserDm
	 */
	public function setPassword($password) {
		$this->_data['password'] = $password;
		$this->password = $password;
		return $this;
	}
	
	public function setOldpwd($password) {
		$this->_data['old_password'] = $password;
		return $this;
	}

	/**
	 * 设置用户email
	 * 
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->_data['email'] = $email;
		return $this;
	}

	/**
	 * 设置安全问题
	 * 
	 * @param string $question
	 */
	public function setQuestion($question) {
		$this->_data['question'] = $question;
		return $this;
	}
	
	public function setAnswer($answer) {
		$this->_data['answer'] = $answer;
		return $this;
	}

	/**
	 * 设置注册IP
	 * 
	 * @param string $regip
	 */
	public function setRegip($regip) {
		$this->_data['regip'] = $regip;
		return $this;
	}
	
	public function setRealname($name) {
		$this->_data['realname'] = $name;
		return $this;
	}
	
	public function setProfile($profile) {
		$this->_data['profile'] = $profile;
		return $this;
	}
	
	/**
	 * 设置注册时间戳
	 * 
	 * @param string $regdate
	 */
	public function setRegdate($regdate) {
		$this->_data['regdate'] = max(0, intval($regdate));
		return $this;
	}

	/**
	 * 设置性别
	 * 
	 * @param int $gender
	 */
	public function setGender($gender) {
		$this->_data['gender'] = intval($gender);
		return $this;
	}

	/**
	 * 设置生日-年
	 * 
	 * @param int $year
	 */
	public function setByear($year) {
		$this->_data['byear'] = intval($year);
		return $this;
	}
	
	/**
	 * 设置生日-月
	 * 
	 * @param string $month
	 */
	public function setBmonth($month) {
		$this->_data['bmonth'] = $month;
		return $this;
	}
	
	/**
	 * 设置生日-日
	 * 
	 * @param string $bday
	 */
	public function setBday($bday) {
		$this->_data['bday'] = $bday;
		return $this;
	}

	/**
	 * 设置家庭地址代码
	 * 
	 * @param int $hometown
	 */
	public function setHometown($hometown) {
		$this->_data['hometown'] = intval($hometown);
		return $this;
	}

	/**
	 * 设置居住地代码
	 * 
	 * @param int $location
	 */
	public function setLocation($location) {
		$this->_data['location'] = intval($location);
		return $this;
	}

	/**
	 * 设置主页
	 * 
	 * @param string $homepage
	 */
	public function setHomepage($homepage) {
		$this->_data['homepage'] = $homepage;
		return $this;
	}

	/**
	 * 设置QQ号码
	 * 
	 * @param stirng $qq
	 */
	public function setQq($qq) {
		$this->_data['qq'] = $qq;
		return $this;
	}

	/**
	 * 设置msn
	 * 
	 * @param stirng $msn
	 */
	public function setMsn($msn) {
		$this->_data['msn'] = $msn;
		return $this;
	}

	/**
	 * 设置阿里旺旺号码
	 * 
	 * @param string $aliww
	 */
	public function setAliww($aliww) {
		$this->_data['aliww'] = $aliww;
		return $this;
	}

	/**
	 * 设置手机号码
	 * 
	 * @param string $mobile
	 */
	public function setMobile($mobile) {
		$this->_data['mobile'] = $mobile;
		return $this;
	}

	/**
	 * 设置支付帐号
	 * 
	 * @param string $alipay
	 */
	public function setAlipay($alipay) {
		$this->_data['alipay'] = $alipay;
		return $this;
	}
	
	/**
	 * 
	 * 更新消息数
	 * @param int $num
	 */
	public function addMessages($num) {
		$this->_increaseData['messages'] = intval($num);
		return $this;
	}
	
	/**
	 * 设置未读消息数
	 * 
	 * @param int $messages
	 */
	public function setMessageCount($messages){
		$this->_data['messages'] = intval($messages);
	}
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::beforeAdd()
	 */
	protected function _beforeAdd() {
		Wind::import("WSRV:user.validator.WindidUserValidator");
		(($result = WindidUserValidator::checkName($this->_data['username'])) === true) &&
			(($result = WindidUserValidator::checkEmail($this->_data['email'])) === true) &&
			(($result = WindidUserValidator::checkPassword($this->_data['password'])) === true);
		if ($result !== true) return $result;
		$this->_data['salt'] = WindUtility::generateRandStr(6);
		$this->_data['password'] = WindidUtility::buildPassword($this->_data['password'], $this->_data['salt']);
		if (isset($this->_data['question']) && isset($this->_data['answer'])){
			$this->_data['safecv'] = $this->_data['question'] ? substr(md5($this->_data['question'] . $this->_data['answer']), 8, 8) : '';
		}
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::beforeUpdate()
	 */
	protected function _beforeUpdate() {
		Wind::import("WSRV:user.validator.WindidUserValidator");
		if (!$this->uid) return new WindidError(WindidError::FAIL);
		if (isset($this->_data['username'])) {
			$result = WindidUserValidator::checkName($this->_data['username'], $this->uid);
			if ($result !== true) return $result;
		}
		if (isset($this->_data['email'])) {
			$result = WindidUserValidator::checkEmail($this->_data['email'], $this->uid);
			if ($result !== true) return $result;
		}
		if (isset($this->_data['old_password'])) {
			$result = WindidUserValidator::checkOldPassword($this->_data['old_password'], $this->uid);
			if ($result !== true) return $result;
		}
		if (isset($this->_data['password'])) {
			$this->_data['salt'] = WindUtility::generateRandStr(6);
			$this->_data['password'] = WindidUtility::buildPassword($this->_data['password'], $this->_data['salt']);
		}
		if (isset($this->_data['question']) && isset($this->_data['answer'])){
			$this->_data['safecv'] = $this->_data['question'] ? substr(md5($this->_data['question'] . $this->_data['answer']), 8, 8) : '';
		}
		return true;
	}
}