<?php
Wind::import('WIND:utility.Security');
Wind::import('WIND:utility.WindFile');
/**
 * 文件上传基类
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AbstractWindUpload.php 3172 2011-11-24 07:57:52Z yishuo $
 * @package upload
 */
abstract class AbstractWindUpload {
	
	/**
	 * 是否有错误产生
	 * 
	 * @var boolean
	 */
	protected $hasError = false;
	
	/**
	 * 错误信息
	 * 
	 * @var array
	 */
	protected $errorInfo = array('type' => array(), 'size' => array(), 'upload' => array());
	
	/** 
	 * 允许的类型
	 * 
	 * @var array
	 */
	protected $allowType = array();//允许上传的类型及对应的大小，array(ext=>size);

	/**
	 * 上传文件
	 * 
	 * @param string $saveDir 文件保存的目录
	 * @param string $preFileName 文件保存的前缀
	 * @param array  $allowType  允许的格式array(ext=>size) size单位为b
	 * <code>
	 * array(
	 *  'jpg' => 1024,
	 *  'gif => 1000,
	 * </code> 
	 * @return array 返回上传成功的文件
	 */
	public function upload($saveDir, $preFileName = '', $allowType = array()) {
		$this->setAllowType($allowType);
		$uploaddb = array();
		foreach ($_FILES as $key => $value) {
			if (is_array($value['name'])) {
				$temp = $this->multiUpload($key, $saveDir, $preFileName);
				$uploaddb[$key] = isset($uploaddb[$key]) ? array_merge((array)$uploaddb[$key], $temp) : $temp;
			} else {
				$uploaddb[$key][] = $this->simpleUpload($key, $saveDir, $preFileName);
			}
		}
		return 1 == count($uploaddb) ? array_shift($uploaddb) : $uploaddb;
	}
	
	/**
	 * 多文件上传
	 * 
	 * 多个控件
	 * 一个表单中拥有多个上传文件的控件
	 * 
	 * @param string $key 文件的key
	 * @param string $saveDir 文件保存的目录
	 * @param string $preFileName 保存文件的前缀默认为空字串
	 * @return array 返回上传成功之后的文件信息
	 */
	private function simpleUpload($key, $saveDir, $preFileName = '') {
		return $this->doUp($key, $_FILES[$key], $saveDir, $preFileName);
	}
	
	/**
	 * 多文件上传
	 * 
	 * 多个控件
	 * 一个表单中拥有多个上传文件的控件
	 * 
	 * @param string $key 文件的key
	 * @param string $saveDir 文件保存的目录
	 * @param string $preFileName 保存文件的前缀默认为空字串
	 * @return array 返回上传成功之后的文件信息
	 */
	private function multiUpload($key, $saveDir, $preFileName = '') {
		$uploaddb = array();
		$files = $_FILES[$key];
		$num = count($files['name']);
		for($i = 0; $i < $num; $i ++) {
			$one = array();
			$one['name'] = $files['name'][$i];
			$one['tmp_name'] = $files['tmp_name'][$i];
			$one['error'] = $files['error'][$i];
			$one['size'] = $files['size'][$i];
			$one['type'] = $files['type'][$i];
			if (!($upload = $this->doUp($key, $one, $saveDir, $preFileName))) continue;
			$uploaddb[] = $upload;
		}
		return $uploaddb;
	}
	
	/**
	 * 执行上传操作
	 * 
	 * @param string $tmp_name 临时文件
	 * @param string $filename 目的文件名
	 * @return bool 
	 */
	abstract protected function postUpload($tmp_name, $filename);
	
	/**
	 * 返回是否含有错误
	 * 
	 * @return boolean
	 */
	public function hasError() {
		return $this->hasError;
	}
	
	/**
	 * 返回错误信息
	 * 
	 * @param string $errorType 错误类型,可选参数为:
	 * <ul>
	 * <li>'type': 类型出错而不能上传的文件信息,</li>
	 * <li>'size': 超过指定大小而上传失败的文件信息<li>
	 * <li>'upload': 文件不能上传过程出现错误的文件信息</li>
	 * </ul>默认为空，则返回所有上述类型的错误信息
	 * @return array
	 */
	public function getErrorInfo($errorType = '') {
		return isset($this->errorInfo[$errorType]) ? $this->errorInfo[$errorType] : $this->errorInfo;
	}
	
	/**
	 * 设置允许上传的类型
	 * 
	 * @param array $allowType 允许上传的格式配置
	 * @return void
	 */
	public function setAllowType($allowType) {
		$allowType && $this->allowType = $allowType;	
	}
	
	/**
	 * 检查文件是否允许上传
	 * 
	 * @param string $ext 文件的后缀
	 * @return bool 如果在允许的范围则返回true，否则返回false
	 */
	protected function checkAllowType($ext) {
		$allowType = array_keys((array)$this->allowType);
		return $allowType ? in_array($ext, $allowType) : true;
	}
	
	/**
	 * 检查上传文件的大小
	 * 
	 * @param string $type 文件的类型
	 * @param string $uploadSize 上传文件的大小
	 * @return bool 如果上传文件超过指定允许上传的大小则返回false,否则返回true
	 */
	protected function checkAllowSize($type, $uploadSize) {
		if ($uploadSize < 0) return false;
		if (!$this->allowType || !$this->allowType[$type]) return true;
		return $uploadSize < $this->allowType[$type];
	}
	

	/**
	 * 获得文件名字
	 * 
	 * @param array $attInfo 上传文件的信息
	 * @param string $preFileName 文件的前缀
	 * @return string 上传文件的名字
	 */
	protected function getFileName($attInfo, $preFileName = '') {
		$fileName = mt_rand(1, 10) . time() . substr(md5(time() . $attInfo['attname'] . mt_rand(1, 10)), 10, 15) . '.' . $attInfo['ext'];
		return $preFileName ? $preFileName . $fileName : $fileName;
	}

	/**
	 * 获得保存路径
	 * 
	 * @param string $fileName 保存的文件名字
	 * @param string $saveDir 保存文件的路径
	 * @return string 上传后的保存文件的完整路径
	 */
	protected function getSavePath($fileName, $saveDir) {
		return $saveDir ? rtrim($saveDir, '\\/') . '/' . $fileName : $fileName;
	}
	
	/**
	 * 判断是否有上传文件
	 * 
	 * @param string $tmp_name 临时上传文件
	 * @return boolean 如果该文件可以被上传则返回true，否则返回false
	 */
	protected function isUploadFile($tmp_name) {
		if (!$tmp_name || $tmp_name == 'none') {
			return false;
		} elseif (function_exists('is_uploaded_file') && !is_uploaded_file($tmp_name) && !is_uploaded_file(str_replace('\\\\', '\\', $tmp_name))) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * 初始化上传的文件信息
	 * 
	 * @param string $key 上传文件的key
	 * @param string $value 上传文件的信息
	 * @param string $preFileName 上传文件的前缀
	 * @param string $saveDir 上传文件保存路径
	 * @return array 返回文件上传的信息
	 */
	protected function initUploadInfo($key, $value, $preFileName, $saveDir) {
		$arr = array('attname' => $key, 'name' => $value['name'], 'size' => $value['size'], 'type' => $value['type'], 'ifthumb' => 0, 'fileuploadurl' => '');
		$arr['ext'] = strtolower(substr(strrchr($arr['name'], '.'), 1));
		$arr['filename'] = $this->getFileName($arr, $preFileName);
		$arr['fileuploadurl'] = $this->getSavePath($arr['filename'], $saveDir);
		return $arr;
	}
	
	
	/**
	 * 判断是否使图片，如果使图片则返回
	 * 
	 * @param string $ext 文件后缀
	 * @return boolean 如果该文件允许被上传则返回true，否则返回false
	 */
	protected function isImage($ext) {
		return in_array($ext, array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'swf'));
	}
	
	/**
	 * 执行上传操作
	 * 
	 * @param string $key 上传文件的Key值
	 * @param array $value 文件的上传信息
	 * @param string $saveDir 上传文件的保存路径
	 * @param string $preFileName 上传文件的前缀
	 * @return array 上传成功后的文件信息
	 */
	protected function doUp($key, $value, $saveDir, $preFileName) {
		if (!$this->isUploadFile($value['tmp_name'])) return array();
		$upload = $this->initUploadInfo($key, $value, $preFileName, $saveDir);
		
		if (empty($upload['ext']) || !$this->checkAllowType($upload['ext'])) {
			$this->errorInfo['type'][$key][] = $upload;
			$this->hasError = true;
			return array();
		}
		if (!$this->checkAllowSize($upload['ext'], $upload['size'])) {
			$upload['maxSize'] = $this->allowType[$upload['ext']];
			$this->errorInfo['size'][$key][] = $upload;
			$this->hasError = true;
			return array();
		}
		if (!($uploadSize = $this->postUpload($value['tmp_name'], $upload['fileuploadurl']))) {
			$this->errorInfo['upload'][$key][] = $upload;
			$this->hasError = true;
			return array();
		}
		$upload['size'] = intval($uploadSize);
		return $upload;
	}
}