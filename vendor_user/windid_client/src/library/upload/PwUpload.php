<?php

defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:upload.PwUploadFile');

/**
 * 上传组件
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwUpload.php 28967 2013-05-31 11:40:43Z jieyin $
 * @package upload
 */

class PwUpload {

	protected $bhv;		// 上传行为配置
	protected $store;	// 附件存储器

	public function __construct(PwUploadAction $bhv) {
		$this->bhv = $bhv;
		$this->setStore();
	}

	public function getBehavior() {
		return $this->bhv;
	}
	
	/**
	 * 检查是否可以上传
	 *
	 * @return bool|PwError
	 */
	public function check() {
		return $this->bhv->check();
	}
	
	/**
	 * 获取已上传附件个数
	 *
	 * @return int
	 */
	public function getUploadNum() {
		return $this->bhv->getUploadNum();
	}
	
	/**
	 * 检查上传文件是否符合规定
	 *
	 * @param PwUploadFile $file
	 * @return bool|PwError
	 */
	public function checkFile($file) {
		if (!$file->ext || !isset($this->bhv->ftype[$file->ext])) {
			return new PwError(array('upload.ext.error', array('{ext}' => '.' . $file->ext)));
		}
		if ($file->size < 1) {
			return new PwError('upload.size.less');
		}
		if ($file->size > $this->bhv->ftype[$file->ext] * 1024) {
			return new PwError(array('upload.size.over', array('{size}' => $this->bhv->ftype[$file->ext])));
		}
		return true;
	}
	
	/**
	 * 设置附件存储对象
	 */
	public function setStore() {
		$this->store = Wind::getComponent($this->bhv->isLocal ? 'localStorage' : 'storage');
	}
	
	/**
	 * 过滤文件名
	 *
	 * @param string $filename
	 * @return string
	 */
	public function filterFileName($filename) {
		return preg_replace('/\.(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)$/i', ".scp_\\1" , $filename);
	}
	
	/**
	 * 上传附件主流程
	 *
	 * @return mixed
	 */
	public function execute() {
		$uploaddb = array();
		foreach ($_FILES as $key => $value) {
			if (!self::isUploadedFile($value['tmp_name']) || !$this->bhv->allowType($key)) {
				continue;
			}
			$file = new PwUploadFile($key, $value);
			if (($result = $this->checkFile($file)) !== true) {
				return $result;
			}
			$file->filename = $this->filterFileName($this->bhv->getSaveName($file));
			$file->savedir = $this->bhv->getSaveDir($file);
			$file->source = $this->store->getAbsolutePath($file->filename, $file->savedir);

			if (!self::moveUploadedFile($value['tmp_name'], $file->source)) {
				return new PwError('upload.fail');
			}
			if (($result = $file->operate($this->bhv, $this->store)) !== true) {
				$this->bhv->fileError($file);
				return $result;
			}
			if (($result = $this->saveFile($file)) !== true) {
				return $result;
			}
			$uploaddb[] = $file->getInfo();
		}
		return $this->bhv->update($uploaddb);
	}
	
	/**
	 * 保存文件
	 *
	 * @param PwUploadFile $file
	 * @return bool|PwError
	 */
	public function saveFile($file) {
		if (($result = $this->store->save($file->source, $file->fileuploadurl)) !== true) {
			return $result;
		}
		if ($thumb = $file->getThumb()) {
			foreach ($thumb as $key => $value) {
				$this->store->save($value[0], $value[1]);
			}
		}
		return true;
	}
	
	/**
	 * 统计待上传附件个数
	 *
	 * @return int
	 */
	public static function countUploadedFile() {
		$i = 0;
		foreach ($_FILES as $key => $value) {
			if (self::isUploadedFile($value['tmp_name'])) $i++;
		}
		return $i;
	}
	
	/**
	 * 判断是否是正常的上传文件
	 *
	 * @param string $tmp_name
	 * @return bool
	 */
	public static function isUploadedFile($tmp_name) {
		if (!$tmp_name || $tmp_name == 'none') {
			return false;
		} elseif (function_exists('is_uploaded_file') && !is_uploaded_file($tmp_name) && !is_uploaded_file(str_replace('\\\\', '\\', $tmp_name))) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * 移动上传文件
	 *
	 * @param string $tmp_name 源文件
	 * @param string $filename 移动后的文件地址
	 * @return bool
	 */
	public static function moveUploadedFile($tmp_name, $filename) {
		if (strpos($filename, '..') !== false || strpos($filename, '.php.') !== false || preg_match("/\.php$/i", $filename)) {
			exit('illegal file type!');
		}
		self::createFolder(dirname($filename));
		if (function_exists("move_uploaded_file") && @move_uploaded_file($tmp_name, $filename)) {
			@chmod($filename, 0777);
			return true;
		}
		if (self::copyFile($tmp_name, $filename)) {
			return true;
		}
		return false;
	}
	
	/**
	 * 复制文件
	 *
	 * @param string $srcfile 源文件
	 * @param string $dstfile 目标文件地址
	 * @return bool
	 */
	public static function copyFile($srcfile, $dstfile) {
		if (@copy($srcfile, $dstfile)) {
			@chmod($dstfile, 0777);
			return true;
		}
		if (is_readable($srcfile)) {
			file_put_contents($dstfile, file_get_contents($srcfile));
			if (file_exists($dstfile)) {
				@chmod($dstfile, 0777);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 创建目录
	 *
	 * @param string $path
	 */
	public static function createFolder($path) {
		if (!is_dir($path)) {
			self::createFolder(dirname($path));
			@mkdir($path);
			@chmod($path, 0777);
			@fclose(@fopen($path . '/index.html', 'w'));
			@chmod($path . '/index.html', 0777);
		}
	}

	public function __call($methodName, $args) {
		if (!method_exists($this->bhv, $methodName)) {
			return false;
		}
		$method = new ReflectionMethod($this->bhv, $methodName);
		if ($method->isPublic()) {
			return call_user_func_array(array(&$this->bhv, $methodName), $args);
		}
		return false;
	}
}
?>