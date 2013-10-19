<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:ubb.config.PwUbbCodeConvertConfig');

/**
 * ubb转换配置
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwUbbCodeConvertThread.php 24066 2013-01-21 07:30:33Z jinlong.panjl $
 * @package lib.utility
 */

class PwUbbCodeConvertThread extends PwUbbCodeConvertConfig {
	
	public $thread;
	public $post;
	public $pid;
	public $user;
	public $attach;
	public $isFeature = false;

	protected static $_ispost = array();
	protected static $_isbuy = array();

	public function __construct(PwThreadBo $thread = null, $post = array(), PwUserBo $user = null) {
		$config = Wekit::C('bbs');
		$this->isConvertPost = true;
		$this->isConvertHide = true;
		$this->isConvertSell = true;
		$this->isConverImg = $config['ubb.img.open'];
		$this->isConvertMedia = $config['ubb.media.open'] ? 2 : 1;
		$this->isConvertFlash = $config['ubb.flash.open'];
		$this->isConvertTable = 4;
		$this->isConvertIframe = $config['ubb.iframe.open'] ? 2 : 1;

		$this->cvtimes = $config['ubb.cvtimes'];
		$this->imgWidth = $config['ubb.img.width'];
		$this->imgHeight = $config['ubb.img.height'];
		$this->maxSize = $config['ubb.size.max'];
		
		if ($thread && $post && $user) {
			$this->isFeature = true;
			$this->thread = $thread;
			$this->post = $post;
			$this->user = $user;
			$this->pid = $post['pid'];
		}
	}

	public function setAttachParser($attach) {
		$this->attach = $attach;
	}
	
	/**
	 * 是否已回复该帖
	 */
	public function isPost() {
		if (!$this->isFeature || !$this->user->isExists()) {
			return 0;
		}
		$tid = $this->thread->tid;
		if (!isset(self::$_ispost[$tid])) {
			if ($this->thread->info['created_userid'] == $this->user->uid) {
				self::$_ispost[$tid] = 2;
			} elseif (Wekit::load('forum.PwThread')->countPostByTidAndUid($tid, $this->user->uid) > 0) {
				self::$_ispost[$tid] = 1;
			} else {
				self::$_ispost[$tid] = 0;
			}
		}
		return self::$_ispost[$tid];
	}

	public function isLogin() {
		return ($this->user && $this->user->isExists());
	}

	public function isAuthor() {
		return ($this->isFeature && $this->post['created_userid'] == $this->user->uid);
	}

	public function checkCredit($cValue, $cType) {
		if (!$this->isFeature) return false;
		if ($this->isAuthor()) return true;
		return ($this->user->getCredit($cType) >= $cValue);
	}

	public function isBuy() {
		if (!$this->isFeature || !$this->user->isExists()) {
			return false;
		}
		if ($this->isAuthor()) {
			return true;
		}
		$tid = $this->thread->tid;
		isset(self::$_isbuy[$tid]) || self::$_isbuy[$tid] = Wekit::load('forum.PwThreadBuy')->getByTidAndUid($tid, $this->user->uid);
		if (self::$_isbuy[$tid] && isset(self::$_isbuy[$tid][$this->pid])) {
			return true;
		}
		return false;
	}

	public function getSellInfo() {
		return array(
			WindUrlHelper::createUrl('bbs/buythread/record', array('tid' => $this->thread->tid, 'pid' => $this->pid)),
			WindUrlHelper::createUrl('bbs/buythread/buy', array('tid' => $this->thread->tid, 'pid' => $this->pid)),
			$this->pid ? $this->post['sell_count'] : $this->thread->info['sell_count']
		);
	}

	public function getUserCredit($cType) {
		return $this->user ? $this->user->getCredit($cType) : 0;
	}

	public function getAttachHtml($aid) {
		if ($this->attach) {
			return $this->attach->getHtml($this->pid, $aid);
		}
		return '[附件]';
	}

	public function removeAttach($aids) {
		$this->attach && $this->attach->removeAttach($aids);
	}
}