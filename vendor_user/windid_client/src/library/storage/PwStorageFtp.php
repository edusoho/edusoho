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

class PwStorageFtp {

	private $_config;
	private $_ftp = null;

	public function __construct() {
		$this->_config = Wekit::C('attachment');
	}
	
	/**
	 * 获取web地址
	 *
	 * @param string $path 相对存储地址
	 * @param int $ifthumb 是否获取缩略图
	 * @return string
	 */
	public function get($path, $ifthumb) {
		$dir = '';
		if ($ifthumb & 2) {
			$dir = 'thumb/mini/';
		} elseif ($ifthumb & 1) {
			$dir = 'thumb/';
		}
		return $this->_config['ftp.url'] . '/' . $dir . $path;
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
	public function save($source, $filePath) {
		$this->_getFtp()->upload($source, $filePath);
		Pw::deleteFile($source);
		return true;
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
		$this->_getFtp()->delete($path);
		if ($ifthumb) {
			($ifthumb & 1) && $this->_getFtp()->delete('thumb/' . $path);
			($ifthumb & 2) && $this->_getFtp()->delete('thumb/mini/' . $path);
		}
		return true;
	}

	public function _getFtp() {
		if ($this->_ftp == null) {
			Wind::import('WIND:ftp.WindSocketFtp');
			$this->_ftp = new WindSocketFtp(array(
				'server' => $this->_config['ftp.server'],
				'port' => $this->_config['ftp.port'],
				'user' => $this->_config['ftp.user'],
				'pwd' => $this->_config['ftp.pwd'],
				'dir' => $this->_config['ftp.dir'],
				'timeout' => $this->_config['ftp.timeout'],
			));
		}
		return $this->_ftp;
	}
}
?>