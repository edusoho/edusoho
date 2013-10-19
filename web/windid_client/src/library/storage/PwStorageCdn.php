<?php
defined('WEKIT_VERSION') || exit('Forbidden');
@set_time_limit('800');
/**
 * 上传组件
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwStorageFtp.php 22526 2012-12-25 07:18:36Z yishuo $
 * @package upload
 */

class PwStorageCdn {

	private $_config;
	private $_cdn = null;

	public function __construct() {
		$this->_config = Wekit::C('attachs');
	}
	
	/**
	 * 获取web地址
	 *
	 * @param string $path 相对存储地址
	 * @param int $ifthumb 是否获取缩略图
	 * @return string
	 */
	public function get($path, $ifthumb) {
		return $this->_config['dnsurl'] . '/' .  $path;
	}
	
	/**
	 * 获取下载地址
	 *
	 * @param string $path
	 * @return string 文件真实存储路径
	 */
	public function getDownloadUrl($path) {
		return $this->get($path, 0);
	}
	
	/**
	 * 存储附件,如果是远程存储，记得删除本地文件
	 *
	 * @param string $source 本地源文件地址
	 * @param string $filePath 存储相对位置
	 * @return bool
	 */
	public function save($source, &$filePath) {
		$data = WindFile::read($source);
		$stuff = WindFile::getSuffix($source);
		$result = $this->_getCdn()->write($data, $stuff);
		if ($result){
			Pw::deleteFile($source);
			$filePath = $result;
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 获取附件上传时存储在本地的文件地址
	 *
	 * @param string $filename 文件名
	 * @param string $dir 目录名
	 * @return string
	 */
	public function getAbsolutePath($filename, $dir) {
		return DATA_PATH . 'upload/' . Pw::time2str(WEKIT_TIMESTAMP, 'j') . '/' . str_replace('/', '_', $dir) . $filename;
	}
	
	/**
	 * 删除附件
	 *
	 * @param string $path 附件地址
	 */
	public function delete($path, $ifthumb = 0) {
		$result = $this->_getCdn()->delete($path);
		return true;
	}

	public function _getCdn() {
		if ($this->_cdn == null) {
			Wind::import('SRV:upload.cdn.PwAliCdn');
			$this->_cdn = new PwAliCdn();
			$this->_cdn->set_version(RAW_VERSION);
			$this->_cdn->set_appkey($this->_config['dnsappkey']);
		}
		return $this->_cdn;
	}
}
?>