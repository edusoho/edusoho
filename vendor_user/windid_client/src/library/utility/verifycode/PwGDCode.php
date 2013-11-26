<?php
Wind::import('LIB:utility.verifycode.PwBaseCode');
 /**
  * GD库验证码
  * 
  * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
  * @author $Author: gao.wanggao $ foxsee@aliyun.com
  * @version $Id: PwGDCode.php 23362 2013-01-09 04:19:41Z gao.wanggao $ 
  * @package 
  */
class PwGDCode extends PwBaseCode {
	
	private static $_background;
	
	private static $_graph;
	
	private static $_font;
	
	private static $_size;
	
	private static $_angle;
	
	private static $_color;
	
	private static $_codeLen = 0;
	
	private static $_codeX = 0;
	
	private static $_codeY = 0;
	
	private static $_image;

	public static function init() {
		if(!function_exists("imagecreatetruecolor") && !function_exists("imagecolorallocate") && !function_exists("imagestring") && !function_exists("imagepng") && !function_exists("imagesetpixel") && !function_exists("imagefilledrectangle") && !function_exists("imagerectangle")) {
			return false;
		}
		self::setRandCode();
		return true;
	}
	
	public static function outputImage() {
		 return self::$isRandGif ? (self::_outGif()) : (self::_outPng());
	}
	
	public static function outputFlash() {
		 return self::_outFlash();
	}

	private static function _setRandX() {
		self::$_codeX = floor((self::$verifyWidth -20) / self::$_codeLen);
		
	}
	
	private static function _setRandY() {
		$_y = self::$verifyHeight/2;
		self::$_codeY = mt_rand($_y+5,$_y+10);
	}
	

	private static function _setRandBackground() {
		$red 	= self::$isRandBackground ? mt_rand(100, 200) : 255;
		$green  = self::$isRandBackground ? mt_rand(100, 200) : 255;
		$blue   = self::$isRandBackground ? mt_rand(100, 200) : 255;
		$alpha  = 0;//self::$isRandBackground ? mt_rand(0, 127) : 0;
		self::$_background = array('red'=>$red,'green'=>$green,'blue'=>$blue,'alpha'=>$alpha);
	}
	
	private static function _setPicBackground() {
		$bgs=self::getVerifyBackground();
		$rand = array_rand($bgs);
		$imbg = imagecreatefromjpeg($bgs[$rand]);
		if (!$imbg) return false;
		imagecopymerge(self::$_image, $imbg, 0, 0, mt_rand(0,450-self::$verifyWidth), mt_rand(0,150-self::$verifyHeight), self::$verifyWidth, self::$verifyHeight, 100);
		imagedestroy($imbg);
	}
	
	private static function _setRandFont() {
		$_path=Wind::getRealDir(self::$path.'.font');
		if (self::$verifyType < 5) {
			$_fontList= self::getEnFontList();
		} else {
			$_fontList = self::getCnFontList();
		}
		$key = self::$isRandFont ? array_rand($_fontList,1) : 0;
		self::$_font =  $_path.'/'.$_fontList[$key];
		return;
	}
	
	private static function _setRandSize() {
		self::$_size = self::$isRandSize ? mt_rand(14,20) : 18;
	}
	
	private static function _setRandAngle() {
		self::$_angle = self::$isRandAngle ? mt_rand(-20, 10) : 0;
	}
	
	private static function _setRandColor() {
		if (!self::$isRandColor) {
			self::$_color = imagecolorallocate(self::$_image, 0, 0, 0);
		} else {
			self::$_color =  imagecolorallocate(self::$_image, mt_rand(0, 255), mt_rand(0, 120), mt_rand(0, 255));
		}
	}
	
	private static function _setRandDistortion() {
		if (!self::$isRandDistortion) return true;
		$_tmp = self::$_image;
		self::$verifyWidth = self::$verifyWidth;
		self::_creatImage();
		self::_creatBackground();
        for ( $i=0; $i<self::$verifyWidth; $i++) {
            for ( $j=0; $j<self::$verifyHeight; $j++) {
                $_color = imagecolorat($_tmp, $i , $j);
                if( intval($i+sin($j/self::$verifyHeight*2*M_PI)*10) <= self::$verifyWidth && intval(($i+sin($j/self::$verifyHeight*2*M_PI)*10)) >=0 ) {
               		 imagesetpixel(self::$_image, intval($i+sin($j/self::$verifyHeight*2*M_PI-0.6)*5), $j,$_color);
                }
                
            }
       }
	}
	
	
	private static function _setRandGraph() {
		if (!self::$isRandGraph) return true;
		$_tmp = mt_rand(1,3);
		switch ($_tmp) {
			case '1':
		   		self::_setImageLine();
		    	break;
		    case '2':
		   		self::_setImagePix();
		    	break;
		    case '3':
		   		self::_setImageEarc();
		    	break;
		   /* case '4':
		    	self::_setImageLine();
		   		self::_setImageEarc();
		    	break;
		    case '5':
		   		self::_setImageLine();
		   		//self::_setImagePix();
		    	break;
		    case '6':
		   		//self::_setImagePix();
		   		self::_setImageEarc();
		    	break;*/
		}
	}
	
	private static function _setImageLine() {
		$_tmp = mt_rand(30,40);
		for ($i = 0; $i < $_tmp; $i++) {
			$_x = mt_rand(0, self::$verifyWidth);
			$_y = mt_rand(0, self::$verifyHeight);
			$_color = imagecolorallocate(self::$_image, mt_rand(50, 255), mt_rand(50, 200), mt_rand(25, 200));
			imageline ( self::$_image, $_x, $_y, mt_rand(($_x-10), ($_x+5)), mt_rand(($_y+5), ($_y+20)), $_color);
		}
	}
	
	private static function _setImagePix() {
		$_tmp = mt_rand(600,800);
		for ($i = 0; $i < $_tmp; $i++) {
			$_color = imagecolorallocate(self::$_image, mt_rand(50, 255), mt_rand(50, 200), mt_rand(25, 200));
			imagesetpixel(self::$_image, mt_rand(0, self::$verifyWidth), mt_rand(0, self::$verifyHeight), $_color);	
		}
	}
	private static function _setImageEarc() {
		$_tmp = mt_rand(5,10);
		for ($i = 0; $i < $_tmp; $i++) {
			$_color = imagecolorallocate(self::$_image, mt_rand(50, 255), mt_rand(50, 200), mt_rand(25, 200));
			imagearc(self::$_image, mt_rand(0, self::$verifyWidth), mt_rand(10,self::$verifyHeight), mt_rand(10, self::$verifyWidth), mt_rand(self::$verifyHeight, self::$verifyHeight*2), mt_rand(0, 90), mt_rand(0,90), $_color);
		}
	}
	
	private static function _getCodeLenth() {
		//self::$_codeLen = Pw::strlen(self::$verifyCode);
		self::$_codeLen = WindString::strlen(self::$verifyCode, 'utf-8');
	}
	
	private static function _outFlash() {
		if (!class_exists('SWFBitmap')) return false;
		self::_getCodeLenth();
		self::_creatImage();
		self::_setRandBackground();
		self::_creatBackground();
		//self::_setPicBackground();
		self::_setRandFont();
		self::_setRandGraph();	
		self::_writeImage();
		self::_setRandDistortion();	
		$_tmpPath = Wind::getRealDir('DATA:tmp.');
		$_tmp = $_tmpPath.WindUtility::generateRandStr(8).'.png';
		imagepng(self::$_image, $_tmp);
		if (!WindFile::isFile($_tmp)) return false;
		imagedestroy(self::$_image);
		$bit= new SWFBitmap($_tmp);
		$shape= new SWFShape(); 
		$shape->setRightFill($shape->addFill($bit)); 
		$shape->drawLine($bit->getWidth(),0); 
		$shape->drawLine(0,$bit->getHeight()); 
		$shape->drawLine(-$bit->getWidth(),0); 
		$shape->drawLine(0, -$bit->getHeight()); 
		$movie= new SWFMovie(); 
		$movie->setDimension($bit->getWidth(),$bit->getHeight()); 
		$flash=$movie->add($shape);
		header("Pragma:no-cache");
		header("Cache-control:no-cache");
		header('Content-type: application/x-shockwave-flash'); 
		$movie->output();
		WindFolder::clear($_tmpPath);
	}
	
	private static function _outPng() {
		header("Pragma:no-cache");
		header("Cache-control:no-cache");
		header("Content-type: image/png");
		self::_getCodeLenth();
		self::_creatImage();
		if (self::$isRandBackground) {
			self::_setPicBackground();
		} else {
			self::_setRandBackground();
			self::_creatBackground();
		}
		self::_setRandFont();
		self::_setRandGraph();	
		self::_writeImage();
		self::_setRandDistortion();	
		imagepng(self::$_image);
		imagedestroy(self::$_image);
	}
	
	private static function _outGif() {
		header("Pragma:no-cache");
		header("Cache-control:no-cache");
		header("Content-type: image/gif");
		Wind::import('LIB:utility.verifycode.GifMerge');
		self::_getCodeLenth();
		self::_setRandBackground();
		self::_setRandFont();
		for ($i=0; $i<3; $i++) {
			self::_creatImage();
			self::_creatBackground();
			self::_setRandGraph();
			self::_writeImage();
			ob_start();
			imageGif(self::$_image);
			$frame[] = ob_get_contents();
			$delay[] = 100;
			imagedestroy(self::$_image);
			ob_end_clean();
		}
		$gif = new GifMerge($frame, 0, 0, 0, 0, $delay, 0, 0, 'C_MEMORY');
		echo $gif->getAnimation();
		
	}
	private static function _writeImage() {
		for ($i = 0; $i < self::$_codeLen; $i++) { 
			//$_text = Pw::substrs(self::$verifyCode, 1, $i, false);
			$_text = WindString::substr(self::$verifyCode, $i, 1, 'utf-8', false);
			self::_setRandSize();
			self::_setRandAngle();
			self::_setRandX();
			self::_setRandY();
			self::_setRandColor();
			ImageTTFText(self::$_image, self::$_size, self::$_angle, (self::$_codeX * $i +10) , self::$_codeY, self::$_color, self::$_font,$_text);
		}
	}
	
	private static function _creatImage() {
		self::$_image = imagecreatetruecolor(self::$verifyWidth, self::$verifyHeight);	
		imagesavealpha(self::$_image, true);
	}
	
	private static function _creatBackground() {
		$_tmp = imagecolorallocatealpha(self::$_image, self::$_background['red'], self::$_background['green'], self::$_background['blue'], self::$_background['alpha']); 
		imagefill(self::$_image, 20, 0, $_tmp); 
	}

}
?>