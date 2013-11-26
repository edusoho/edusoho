<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 上传组件
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwStorageLocal.php 24383 2013-01-29 10:09:39Z jieyin $
 * @package upload
 */

class PwStorageLocal {

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
		return Wekit::url()->attach . '/' . $dir . $path;
	}
	
	/**
	 * 获取下载地址
	 *
	 * @param string $path
	 * @return string 文件真实存储路径
	 */
	public function getDownloadUrl($path) {
		return ATTACH_PATH . $path;
	}
	
	/**
	 * 存储附件,如果是远程存储，记得删除本地文件
	 *
	 * @param string $source 本地源文件地址
	 * @param string $filePath 存储相对位置
	 * @return bool
	 */
	public function save($source, $filePath) {
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
		return ATTACH_PATH . $dir . $filename;
	}
	
	/**
	 * 删除附件
	 *
	 * @param string $path 附件地址
	 */
	public function delete($path, $ifthumb = 0) {
		Pw::deleteFile(ATTACH_PATH . $path);
		if ($ifthumb) {
			($ifthumb & 1) && Pw::deleteFile(ATTACH_PATH . 'thumb/' . $path);
			($ifthumb & 2) && Pw::deleteFile(ATTACH_PATH . 'thumb/mini/' . $path);
		}
		return true;
	}
}
?>