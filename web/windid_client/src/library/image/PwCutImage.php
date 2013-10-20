<?php
/**
 * 用于门户的缩略/截取算法
 * 
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwCutImage.php 22257 2012-12-20 09:50:37Z gao.wanggao $ 
 * @package 
 */
class PwCutImage {
	/**
	 * 需处理的文件，绝对路径
	 * 
	 * @var string
	 */
	public $image;
	
	/**
	 * 处理后的图片大小
	 * 
	 * @var int
	 */
	public $cutWidth = 0;
	
	public $cutHeight = 0;
	
	/**
	 * 处理后的图片地址
	 * 
	 * @var string
	 */
	public $outImage;
	
	/**
	 * 图片品质
	 * 
	 * @var int
	 */
	public $quality = 90;
	
	/**
	 * 强制缩略图，空白填充
	 *
	 * @var bool
	 */
	public $forceThumb = false;
	
	/**
	 * true剪切 false按比例缩小
	 *
	 * @var bool
	 */
	public  $forceScale = false;
	
	/**
	 * 图片剪切的左上角坐标
	 *
	 * @var int
	 */
	public $cutX = 0;
	
	public $cutY = 0;
	
	/**
	 * 旋转角度
	 * 
	 * @var int
	 */
	public  $degrees = 90;
	
	private $_imageInfo;
	
	private $_frontImage;
	
	private $_backImage;
	
	/**
	 * 图片剪切或缩略
	 * 
	 */
	public function cut() {
		$this->_getImageInfo();
		if (!$this->_imageInfo) return false;	
		$i_w = $this->_imageInfo[0];
		$i_h = $this->_imageInfo[1];
		$i_scale = $i_w / $i_h;

		if (!$this->cutWidth) {
			$this->cutWidth = $this->cutHeight * $i_scale;
		}
		
		if (!$this->cutHeight) {
			$this->cutHeight = $this->cutWidth / $i_scale;
		}
		
		$c_w = $this->cutWidth;
		$c_h = $this->cutHeight;
		
		$c_scale = $c_w / $c_h;
		
		if ($i_w < $c_w || $i_h < $c_h) {									//原图比缩略还小的，补白或返回原图
			$c_h = $i_h;
			$c_w = $i_w;
		} elseif ($this->forceScale) {										//裁剪
			if ($c_scale >= 1) {  //截宽
				if ($i_scale > 1 && $i_scale > $c_scale) { 					//宽截宽
					$_i_w = $i_w;
					$i_w = ($i_h /$c_h) * $c_w;
					$this->cutX = ($_i_w - $i_w) / 2;
				} else { 													//长截宽
					$i_h = ($i_w / $c_w)  * $c_h; 
					$this->cutX = 0;
				}
			} else {		//截长
				if ($i_scale < 1 && $i_scale < $c_scale) {  				//长截长
					$i_h = ($i_w / $c_w)  * $c_h; 
					$this->cutX = 0;
				} else {													//宽截长
					$_i_w = $i_w;
					$i_w = ($i_h /$c_h) * $c_w;
					$this->cutX = ($_i_w - $i_w) / 2;
				}
			}
		} else {															//缩略
			if ($i_scale > $c_scale) {
				$c_h = $c_w / $i_scale;
			} else { 						
				$c_w = $c_h * $i_scale;
			}

		}
		if (!$this->forceThumb) {											//强制补白
			$this->cutHeight = $c_h;
			$this->cutWidth = $c_w;
		}
		$offsetX = ($this->cutWidth - $c_w) / 2;
		$offsetY = ($this->cutHeight - $c_h) / 2;
		return $this->_cutImage($c_w,$c_h,$i_w,$i_h, $offsetX, $offsetY);
	}
	
	/**
	 * 图片旋转
	 * 
	 */
	public function rotate() {
		$this->_getImageInfo();
		if ($this->_imageInfo == false ) return false;	
		$this->_creatFrontImage();
		$_color = imagecolorallocate($this->_frontImage, 255, 255, 255);
		$this->_backImage = imagerotate($this->_frontImage, $this->degrees, $_color);
		$this->_creatImage();
		return true;
	}
	
	public function getCutImage() {
		return $this->_backImage;
	}
	 	
	private function _getImageInfo() {
		$this->_imageInfo = array();
		if (!file_exists($this->image)) return false;
		$this->_imageInfo = @getimagesize($this->image);
		if (!in_array($this->_imageInfo['mime'], array('image/jpeg','image/gif', 'image/png'))) $this->_imageInfo = array();
	}

	private function _isAnimate() {
		$_content = file_get_contents($this->image);
		return  strpos($_content, 'NETSCAPE2.0') === false ?  false : true;
	}
	
	private function _cutImage($cw,$ch,$iw,$ih, $offsetX = 0, $offsetY = 0) {
		if (!$this->_creatFrontImage()) return false;
		$this->_creatBackImage();
		if (empty($this->_frontImage) && empty($this->_backImage)) return false;
		imagealphablending($this->_backImage, true);
       	imagecolortransparent($this->_backImage, imagecolorallocatealpha($this->_backImage, 0, 0, 0, 0));
        imagecopyresampled($this->_backImage, $this->_frontImage, $offsetX, $offsetY, $this->cutX,  $this->cutY, $cw, $ch, $iw, $ih);
		$this->_creatImage();
		return true;
	}
	
	private  function _creatImage() {
		if (!$this->_createFolder(dirname($this->outImage))) return false;
		switch($this->_imageInfo['mime']) {
			case 'image/jpeg':
				imagejpeg($this->_backImage, $this->outImage, $this->quality);
				break;
			case 'image/gif':
				imagegif($this->_backImage, $this->outImage);
				break;
			case 'image/png':
				imagepng($this->_backImage, $this->outImage);
				break;
			default:
				return false;
		}
		imagedestroy($this->_backImage);
		imagedestroy($this->_frontImage);
	}
	
	private  function _creatFrontImage() {
		switch ($this->_imageInfo['mime']) {
			case 'image/jpeg':
				$this->_frontImage = imagecreatefromjpeg($this->image);
				break;
			case 'image/gif':
				//if (self::_isAnimate()) return false;
				$this->_frontImage = imagecreatefromgif($this->image);
				break;
			case 'image/png':
				$this->_frontImage = imagecreatefrompng($this->image);
				break;
			default:
				return false;
		}
		return true;
	}
	
	private function _creatBackImage() {
		$this->_backImage = imagecreatetruecolor($this->cutWidth, $this->cutHeight);
		$_tmp = imagecolorallocatealpha($this->_backImage, 255, 255, 255, 0); 
		imagefill($this->_backImage, 0, 0, $_tmp); 
	}
	
 	private function _createFolder($path ='') {
		if (!is_dir($path)) {
           $this->_createFolder(dirname($path));
           if (!@mkdir($path,0777)) return false;
           @touch($path."/index.html");
        }
		return true;
	}
 }
?>