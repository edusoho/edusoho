<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 缩略图生成方式
 *
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author Jianmin Chen <sky_hold@163.com>
 * @version $Id: PwImageThumb.php 22380 2012-12-21 14:54:07Z jieyin $
 * @package lib.image
 */

class PwImageThumb {
	
	const TYPE_INTACT = 1; //等比缩略
	const TYPE_CENTER = 2; //居中截取
	const TYPE_DENGBI = 3; //等比填充

	protected $image;

	protected $dstfile;
	protected $width;
	protected $height;
	protected $type;
	protected $quality = 90;
	protected $forcemode = 0;

	protected $thumbWidth;
	protected $thumbHeight;
	
	protected $imageCreateFunc;
	protected $imageCopyFunc;
	protected $imageFunc;

	public function __construct(PwImage $image) {
		$this->image = $image;
	}
	
	/**
	 * 设置缩略图目标地址
	 */
	public function setDstFile($dstfile) {
		$this->dstfile = $dstfile;
	}
	
	/**
	 * 设置宽度
	 */
	public function setWidth($width) {
		$this->width = intval($width);
	}
	
	/**
	 * 设置高度
	 */
	public function setHeight($height) {
		$this->height = intval($height);
	}
	
	/**
	 * 设置缩略方式 <1.等比缩略 2.居中截取 3.等比填充>
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * 设置图片质量
	 */
	public function setQuality($quality) {
		$quality > 0 && $this->quality = $quality;
	}
	
	/**
	 * 是否启用强制模式 <0.当文件尺寸小于缩略要求时，不生成 1.都生成>
	 */
	public function setForceMode($forcemode) {
		$this->forcemode = $forcemode;
	}
	
	/**
	 * 生成缩略图
	 */
	public function execute() {
		if (!$this->dstfile) {
			return -1;
		}
		if ($this->width <= 0 && $this->height <= 0) {
			return -2;
		}
		if (!$this->checkEnv()) {
			return -3;
		}
		if (($compute = $this->compute()) === false) {
			return -4;
		}
		$this->thumbWidth = $compute->canvasW;
		$this->thumbHeight = $compute->canvasH;

		$thumb = call_user_func($this->imageCreateFunc, $compute->canvasW, $compute->canvasH);
		if (function_exists('ImageColorAllocate')) {
			$black = ImageColorAllocate($thumb,255,255,255);
			if ($this->imageCreateFunc == 'imagecreatetruecolor' && function_exists('imagefilledrectangle')) {
				imagefilledrectangle($thumb, 0, 0, $compute->canvasW, $compute->canvasH, $black);
			} elseif ($this->imageCreateFunc == 'imagecreate' && function_exists('ImageColorTransparent')) {
				$bgTransparent = ImageColorTransparent($thumb, $black);
			}
		}
		call_user_func($this->imageCopyFunc, $thumb, $this->image->getSource(), $compute->dstX, $compute->dstY, $compute->srcX, $compute->srcY, $compute->dstW, $compute->dstH, $compute->srcW, $compute->srcH);
		$this->makeImage($thumb, $this->dstfile, $this->quality);
		imagedestroy($thumb);

		return true;
	}
	
	/**
	 * 选用缩略图生成策略
	 */
	public function compute() {
		switch ($this->type) {
			case self::TYPE_CENTER:
				$method = 'PwImageThumbCenterCompute';break;
			default:
				$method = 'PwImageThumbIntactCompute';
		}
		$compute = new $method($this->image, $this->width, $this->height, $this->forcemode);
		if ($compute->compute() === true) {
			return $compute;
		}
		return false;
	}
	
	/**
	 * 生成图片
	 *
	 * @param resource $image 图片内容
	 * @param string $filename 图片地址
	 * @param int $quality 图片质量
	 * return void
	 */
	public function makeImage($image, $filename, $quality = '90') {
		if ($this->image->type == 'jpeg') {
			call_user_func($this->imageFunc, $image, $filename, $quality);
		} else {
			call_user_func($this->imageFunc, $image, $filename);
		}
	}
	
	/**
	 * 检测缩略图环境要求是否满足
	 *
	 * return bool
	 */
	public function checkEnv() {
		if (!$this->image->getSource()) {
			return false;
		}
		$this->imageFunc = 'image' . $this->image->type;
		if (!function_exists($this->imageFunc)) {
			return false;
		}
		if ($this->image->type != 'gif' && function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled')) {
			$this->imageCreateFunc = 'imagecreatetruecolor';
			$this->imageCopyFunc = 'imagecopyresampled';
		} elseif (function_exists('imagecreate') && function_exists('imagecopyresized')) {
			$this->imageCreateFunc = 'imagecreate';
			$this->imageCopyFunc = 'imagecopyresized';
		} else {
			return false;
		}
		return true;
	}

	public function getThumbWidth() {
		return $this->thumbWidth;
	}

	public function getThumbHeight() {
		return $this->thumbHeight;
	}
}

abstract class PwImageThumbCompute {

	public $width;	//缩略限制宽
	public $height;	//缩略限制高

	public $srcX;	//源图像起始x坐标
	public $srcY;	//源图像起始y坐标
	public $srcW;	//源图像选中宽度
	public $srcH;	//源图像选中高度

	public $dstX;	//目标图像起始x坐标
	public $dstY;	//目标图像起始y坐标
	public $dstW;	//目标图像绘制宽度
	public $dstH;	//目标图像绘制高度

	public $canvasW;	//画布宽度
	public $canvasH;	//画布高度
	
	protected $image;
	protected $force = 0;
	
	public function __construct($image, $width, $height, $force = 0) {
		$this->image = $image;
		$this->width = $width;
		$this->height = $height;
		$this->force = $force;
	}

	public function isSmall() {
		return ($this->image->width <= $this->width && $this->image->height <= $this->height);
	}

	public function isWider() {
		return ($this->image->width/$this->width > $this->image->height/$this->height);
	}

	abstract public function compute();
}

/**
 * 等比缩略算法
 */
class PwImageThumbIntactCompute extends PwImageThumbCompute {
	
	public function compute() {

		$this->srcX = 0;
		$this->srcY = 0;
		$this->srcW = $this->image->width;
		$this->srcH = $this->image->height;

		$this->dstX = 0;
		$this->dstY = 0;

		if ($this->width > 0 && $this->height > 0) {
			if ($this->isSmall()) {
				if (!$this->force) return false;
				$this->dstW = $this->image->width;
				$this->dstH = $this->image->height;
			} elseif ($this->isWider()) {
				$this->dstW = $this->width;
				$this->dstH = $this->getThumbHeight();
			} else {
				$this->dstH = $this->height;
				$this->dstW = $this->getThumbWidth();
			}
		} elseif ($this->width > 0 && $this->image->width > $this->width) {
			$this->dstW = $this->width;
			$this->dstH = $this->getThumbHeight();
		} elseif ($this->height > 0 && $this->image->height > $this->height) {
			$this->dstH = $this->height;
			$this->dstW = $this->getThumbWidth();
		} else {
			if (!$this->force) return false;
			$this->dstW = $this->image->width;
			$this->dstH = $this->image->height;
		}
		$this->canvasW = $this->dstW;
		$this->canvasH = $this->dstH;

		return true;
	}

	public function getThumbWidth() {
		return round($this->image->width/$this->image->height * $this->height);
	}

	public function getThumbHeight() {
		return round($this->image->height/$this->image->width * $this->width);
	}
}

/**
 * 居中截取算法
 */
class PwImageThumbCenterCompute extends PwImageThumbCompute {

	public function compute() {
		if ($this->width > 0 && $this->height > 0) {

		} elseif ($this->width > 0) {
			$this->height = $this->width;
		} elseif ($this->height > 0) {
			$this->width = $this->height;
		} else {
			return false;
		}
		if ($this->isSmall()) {
			if (!$this->force) return false;
			$this->srcX = 0;
			$this->srcY = 0;
			$this->srcW = $this->image->width;
			$this->srcH = $this->image->height;
		} elseif ($this->isWider()) {
			$this->srcW = round($this->width/$this->height * $this->image->height);
			$this->srcH = $this->image->height;
			$this->srcX = round(($this->image->width - $this->srcW) / 2);
			$this->srcY = 0;
		} else {
			$this->srcW = $this->image->width;
			$this->srcH = round($this->height/$this->width * $this->image->width);
			$this->srcX = 0;
			$this->srcY = round(($this->image->height - $this->srcH) / 2);
		}
		$this->dstW = min($this->srcW, $this->width);
		$this->dstH = min($this->srcH, $this->height);
		$this->dstX = round(($this->width - $this->dstW) / 2);
		$this->dstY = round(($this->height- $this->dstH) / 2);
		
		$this->canvasW = $this->width;
		$this->canvasH = $this->height;
		
		return true;
	}
}