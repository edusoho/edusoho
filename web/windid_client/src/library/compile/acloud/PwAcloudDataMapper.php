<?php

/**
 * 传递数据map器
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwAcloudDataMapper.php 12670 2012-06-25 07:44:35Z yanchixia $
 * @package wekit.compile.acloud
 */
class PwAcloudDataMapper {
	private $src = '';
	private $charset = 'utf8';
	private $username = '';
	private $uid = 0;
	private $tid = 0;
	private $fid = 0;
	private $title = '';
	
	/**
	 * 获得当前页标识
	 * 
	 * @return string
	 */
	public function getSrc() {
		return $this->src;
	}

	/**
	 * 获得编码
	 * 
	 * @return string
	 */
	public function getCharset() {
		return $this->charset;
	}

	/**
	 * 获得当前登录用户名
	 * 
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * 获得当前登录用户ID
	 * 
	 * @return int
	 */
	public function getUid() {
		return $this->uid;
	}

	/**
	 * 获得当前帖子ID
	 * 
	 * @return int
	 */
	public function getTid() {
		return $this->tid;
	}

	/**
	 * 获得当前版块ID
	 * 
	 * @return int
	 */
	public function getFid() {
		return $this->fid;
	}

	/**
	 * 获得当前帖子标题
	 * 
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * 设置当前页面标识
	 * 
	 * @param string $src
	 */
	public function setSrc($src) {
		$this->src = $src;
	}

	/**
	 * 设置当前使用编码
	 * 
	 * @param string $charset
	 */
	public function setCharset($charset) {
		$this->charset = $charset;
	}

	/**
	 * 设置当前登录用户名
	 * 
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * 设置当前登录用户名
	 * 
	 * @param int $uid
	 */
	public function setUid($uid) {
		$this->uid = $uid;
	}

	/**
	 * 设置当前帖子ID
	 * 
	 * @param int $tid
	 */
	public function setTid($tid) {
		$this->tid = $tid;
	}

	/**
	 * 设置当前版块ID
	 * 
	 * @param int $fid
	 */
	public function setFid($fid) {
		$this->fid = $fid;
	}

	/**
	 * 设置当前帖子标题
	 * 
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
}