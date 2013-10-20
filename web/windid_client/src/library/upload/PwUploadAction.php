<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 上传动作基类/接口
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwUploadAction.php 28884 2013-05-29 02:44:27Z jieyin $
 * @package upload
 */

abstract class PwUploadAction {

	public $ftype = array();
	public $attachs = array();
	public $isLocal = false;
	
	/**
	 * 检测是否允许开始上传行为
	 *
	 * @return bool
	 */
	public function check() {
		return true;
	}
	
	/**
	 * 检测该条上传记录是否是为本次属于业务上传
	 *
	 * @param string $key $_FILES数组中的key
	 * @return bool
	 */
	public function allowType($key) {
		return true;
	}
	
	/**
	 * 获取上传文件存储名
	 *
	 * @param object $file 上传文件对象<PwUploadFile>
	 * @return string
	 */
	abstract public function getSaveName(PwUploadFile $file);
	
	/**
	 * 获取上传文件存储路径
	 *
	 * @param object $file 上传文件对象<PwUploadFile>
	 * @return string
	 */
	abstract public function getSaveDir(PwUploadFile $file);
	
	/**
	 * 是否开启缩略图
	 *
	 * @return bool
	 */
	public function allowThumb() {
		return false;
	}
	
	/**
	 * 缩略图生成配置
	 *
	 * @param string $filename 文件名
	 * @param string $dir 存储路径
	 * @return array 配置 
	 *	例:array(
	 *		array(0.缩略图文件名, 1.缩略图存储地址, 2.限制宽, 3.限制高, 4.缩略图生成方式(*), 5.强制生成(*)),
	 *		array('abc.jpg', 'thumb/mini', 300, 300, 0, 0) 生成多个缩略图时，多条配置
	 *	)
	 * (*4).缩略图生成方式 <0.等比缩略 1.居中截取 2.等比填充>
	 * (*5).强制生成 <0.当文件尺寸小于缩略要求时，不生成 1.都生成>
	 */
	public function getThumbInfo($filename, $dir) {
		return array();
	}
	
	/**
	 * 是否开启图片水印
	 *
	 * @return bool
	 */
	public function allowWaterMark() {
		return false;
	}
	
	/**
	 * 获取水印配置
	 *
	 * @return array 配置,以下选项任意组合
	 *  例:array(
	 *		'type'				=> 1,				<int, 1.图片水印 2.文字水印>
	 *		'gif'				=> 1,				<bool, 是否为gif图片打水印>
	 *		'limitwidth'		=> 200,				<bool, 限制宽>
	 *		'limitheight'		=> 200,				<bool, 限制高> 
	 *		'position'			=> 9,				<bool, 1-9,九宫格位置>
	 *		'transparency'		=> 85,				<bool, 0-100, 透明度>
	 *		'quality'			=> 85,				<bool, 0-100, 水印质量>
	 *		'file'				=> a.gif,			<string, 水印文件>
	 *		'text'				=> abc,				<string, 水印文字>
	 *		'fontfamily'		=> cn_witer.ttf,	<string, 字体>
	 *		'fontsize'			=> 12,				<string, 字号>
	 *		'fontcolor'			=> #aaa				<string, 颜色>
	 *   )
	 */
	public function getWaterMarkInfo() {
		return array();
	}
	
	/**
	 * 附件上传失败，回调函数
	 *
	 * @param PwUploadFile $file
	 * @return void
	 */
	public function fileError(PwUploadFile $file) {
		Pw::deleteFile($file->source);
	}
	
	/**
	 * 上传完成，业务处理逻辑
	 *
	 * @param array $uploaddb 上传文件列表
	 * @return mixed
	 */
	abstract public function update($uploaddb);
	
	/**
	 * 获得上传附件列表
	 *
	 * @return array
	 */
	public function getAttachs() {
		return $this->attachs;
	}
	
	/**
	 * 获得上传附件个数
	 *
	 * @return int
	 */
	public function getUploadNum() {
		return count($this->attachs);
	}
}
?>