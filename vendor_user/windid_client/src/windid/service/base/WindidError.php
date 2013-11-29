<?php

/**
 * Windid工具库
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidError.php 24834 2013-02-22 06:43:43Z jieyin $
 * @package Windid.library
 */

class WindidError {

	const SUCCESS = 1;
	const FAIL = 0;

	const NAME_EMPTY = -1;
	const NAME_LEN = -2;
	const NAME_ILLEGAL_CHAR = -3;
	const NAME_FORBIDDENNAME = -4;
	const NAME_DUPLICATE = -5;
	
	const EMAIL_EMPTY = -6;
	const EMAIL_ILLEGAL = -7;
	const EMAIL_WHITE_LIST = -8;
	const EMAIL_BLACK_LIST = -9;
	const EMAIL_DUPLICATE = -10;

	const PASSWORD_LEN = -11;
	const PASSWORD_ILLEGAL_CHAR = -12;
	const PASSWORD_ERROR = -13;

	const USER_NOT_EXISTS = -14;
	
	const SAFECV_ERROR = -20;
	
	const MESSAGE_CONTENT_LENGTH_ERROR = -30;

	const SCHOOL_NAME_EMPTY = -40;
	const SCHOOL_AREAID_EMPTY = -41;
	const SCHOOL_TYPEID_EMPTY = -42;
	
	const UPLOAD_FAIL = -80;
	const UPLOAD_EXT_ERROR = -81;
	const UPLOAD_SIZE_LESS = -82;
	const UPLOAD_SIZE_OVER = -83;
	const UPLOAD_CONTENT_ERROR = -84;

	const TIMEOUT = -90;
	const CLASS_ERROR = -91;
	const METHOD_ERROR = -92;
	const SERVER_ERROR = -93;
	
	public $errorCode;

	public function __construct($code) {
		$this->errorCode = $code;
	}

	public function getCode() {
		return $this->errorCode;
	}
}