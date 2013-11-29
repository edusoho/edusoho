<?php
Wind::import('WIND:upload.AbstractWindUpload');
/**
 * 表单文件上传
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindFormUpload.php 3228 2011-12-02 06:49:38Z yishuo $
 * @package upload
 */
class WindFormUpload extends AbstractWindUpload {

	/**
	 * 初始化允许用户上传的类型
	 *
	 * @param array $allowType
	 */
	public function __construct($allowType = array()) {
		$this->setAllowType($allowType);
	}

	/*
	 * (non-PHPdoc)
	 * @see AbstractWindUpload::postUpload()
	 */
	protected function postUpload($tmp_name, $filename) {
		if (strpos($filename, '..') !== false || strpos($filename, '.php.') !== false || preg_match('/\.php$/', 
			$filename)) {
			exit('illegal file type!');
		}
		WindFolder::mkRecur(dirname($filename));
		if (function_exists("move_uploaded_file") && @move_uploaded_file($tmp_name, $filename)) {
			@unlink($tmp_name);
			@chmod($filename, 0777);
			return filesize($filename);
		} elseif (@copy($tmp_name, $filename)) {
			@unlink($tmp_name);
			@chmod($filename, 0777);
			return filesize($filename);
		} elseif (is_readable($tmp_name)) {
			Wind::import('WIND:utility.WindFile');
			WindFile::write($filename, WindFile::read($tmp_name));
			@unlink($tmp_name);
			if (file_exists($filename)) {
				@chmod($filename, 0777);
				return filesize($filename);
			}
		}
		return false;
	}
}