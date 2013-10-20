<?php
Wind::import('WIND:upload.AbstractWindUpload');
/**
 * ftp远程文件上传
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindFtpUpload.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package upload
 */
class WindFtpUpload extends AbstractWindUpload {

	private $config = array();

	private $ftp = null;

	/**
	 * 构造函数设置远程ftp链接信息
	 *
	 * @param array $config
	 */
	public function __construct($config) {
		$this->setConfig($config);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindUpload::postUpload()
	 */
	protected function postUpload($tmp_name, $filename) {
		$ftp = $this->getFtpConnection();
		if (!($size = $ftp->upload($tmp_name, $filename))) return false;
		@unlink($tmp_name);
		return $size;
	}

	/**
	 * 设置ftp链接配置文件
	 * 
	 * @param array $config ftp链接信息
	 * @return bool
	 */
	public function setConfig($config) {
		if (!is_array($config)) return false;
		$this->config = $config;
		return true;
	}

	/**
	 * 获得ftp链接对象
	 * 
	 * @return AbstractWindFtp
	 */
	private function getFtpConnection() {
		if (is_object($this->ftp)) return $this->ftp;
		if (function_exists('ftp_connect')) {
			Wind::import("WIND:ftp.WindFtp");
			$this->ftp = new WindFtp($this->config);
			return $this->ftp;
		}
		Wind::import("WIND:ftp.WindSocketFtp");
		$this->ftp = new WindSocketFtp($this->config);
		return $this->ftp;
	}
}