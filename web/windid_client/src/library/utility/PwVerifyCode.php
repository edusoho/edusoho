<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 验证码接口
 * 
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwVerifyCode.php 23697 2013-01-15 05:17:30Z jieyin $ 
 * @package 
 */
class PwVerifyCode {
	
	private $_config = '';
	
	public function __construct() {
		$this->_config = Wekit::C('verify');
	}
	
	/**
	 * 验证验证码
	 * 
	 */
	public function checkVerify($inputCode='') {
		if ($inputCode===false || $inputCode==='') return false;
		$inputCode = Pw::encrypt(strtolower($inputCode));
		if ($inputCode === self::_readVerifyCode()) return true;
		return false;
	}
	
	/**
	 * 读取语音验证码
	 * 
	 */
	public function getAudioVerify() {
		if ($this->_config['voice']) {
			Wind::import('LIB:utility.verifycode.PwAudioCode');
			PwAudioCode::$verifyLength = $this->_config['content.length'];
			PwAudioCode::$verifyType = 7;
			PwAudioCode::init();
			$this->_saveVerifyCode();
			PwAudioCode::outAudio();
			return true;
		} 
		return false;
	}

	public function getVerify() {
		$_tmps =array();
		Wind::import('LIB:utility.verifycode.PwGDCode');
		$_tmps = $this->_config['content.type'];
		$_key = array_rand($_tmps,1);
		switch ($_tmps[$_key]) {
			case '1':
			case '2':
		 	case '3':
		 	case '5':
		 		PwGDCode::$verifyLength = $this->_config['content.length'];
		   		PwGDCode::$verifyType = $_tmps[$_key];
		   		PwGDCode::$verifyWidth = $this->_config['width'];
		   		PwGDCode::$verifyHeight = $this->_config['height'];
		   	 	break;
		   	case '4':
		    	$askAnswer = $this->_getVerifyCalculate();
		    	PwGDCode::$verifyType = $_tmps[$_key];
		    	PwGDCode::$askCode = $this->_config['content.showanswer'] ? $askAnswer['ask'].'(' .$askAnswer['answer'].')' : $askAnswer['ask'];
		    	PwGDCode::$answerCode = $askAnswer['answer'];
		   		PwGDCode::$verifyWidth = $this->_config['width'];
		   		PwGDCode::$verifyHeight = $this->_config['height'];
		    	break;
		    case '6':
		    	$askAnswer = $this->_getVerifyAsk();
		    	//PwGDCode::$verifyWidth = 300;
		   		PwGDCode::$verifyType = $_tmps[$_key];
		   		PwGDCode::$askCode = $this->_config['content.showanswer'] ? $askAnswer['ask'].'(' .$askAnswer['answer'].')' : $askAnswer['ask'];
		   		PwGDCode::$answerCode = $askAnswer['answer'];
		   		PwGDCode::$verifyWidth = $this->_config['width'];
		   		PwGDCode::$verifyHeight = $this->_config['height'];
		    	break;
		    default:
		    	PwGDCode::$verifyLength = 4;
		   		PwGDCode::$verifyType = 3;
		   		PwGDCode::$verifyWidth = $this->_config['width'];
		   		PwGDCode::$verifyHeight = $this->_config['height'];
		    	break;		
		}
		$this->_getGDRandType();
		if (!PwGDCode::init()) return false;
		$this->_saveVerifyCode();
		if ($this->_config['type'] == 'image') {
			PwGDCode::outputImage();
		} else {
			PwGDCode::outputFlash();	
		}
		
	}
	
	private function _getVerifyAsk() {
		$questions = $this->_config['content.questions'];
		$_key = array_rand($questions,1);
		return $questions[$_key];
	}
	
	private function _getVerifyCalculate() {
		$c = rand(0,1);
		if ($c) {
			$a = rand(1,70);
			$b = rand(1,30);
			return array('ask'=>strval($a) .' + ' . strval($b) . '= ?', 'answer'=>$a + $b);
		} else {
			$a = rand(50,100);
			$b = rand(1,50);
			return array('ask'=>strval($a) .' - ' . strval($b) . '= ?', 'answer'=>$a - $b);
		}
		
	}
	
	private function _getGDRandType() {
		$isRands = array('isRandBackground','isRandGraph','isRandFont','isRandSize','isRandAngle','isRandColor','isRandGif','isRandDistortion');
		foreach ((array)$this->_config['randtype'] AS $rand) {
			$rand = 'isRand'.ucfirst($rand);
			if (in_array($rand, $isRands)) {
				PwGDCode::$$rand = true;
			}
		}
	}
	
	private function _readVerifyCode() {
		return Pw::getCookie('Pw_verify_code');
		/*Wind::import('WIND:http.session.WindSession');
		$session = new WindSession();
		return $session->get('verifycode');*/
	}
	
	private function _saveVerifyCode() {
		Wind::import('LIB:utility.verifycode.PwBaseCode');
		$code = WindConvert::convert(PwBaseCode::getCode(), Wekit::V('charset'), 'UTF-8');
		$code = Pw::encrypt(strtolower($code));
		//Wind::import('WIND:http.session.WindSession');
		Pw::setCookie('Pw_verify_code',$code ,3600);
		/*$session = new WindSession();
		$session->set('verifycode', $code);*/
	}
}
?>