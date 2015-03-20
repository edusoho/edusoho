<?php
/**
 * 图片处理类库
 * 
 * 包括图片压缩和图片加水印
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindImage.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package utility
 */
class WindImage {
	
	/**
	 * 生成略缩图
	 * 
	 * @param string $srcFile     		源图片
	 * @param string $dstFile     		略缩图保存位置
	 * @param int $dstW           		略缩图宽度
	 * @param string $dstH        		略缩图高度
	 * @param string $isProportion      略缩图是否等比略缩,默认为false
	 * @return array|boolean
	 */
	public static function makeThumb($srcFile, $dstFile, $dstW, $dstH, $isProportion = FALSE) {
		if (false === ($minitemp = self::getThumbInfo($srcFile, $dstW, $dstH, $isProportion))) return false;
		list($imagecreate, $imagecopyre) = self::getImgcreate($minitemp['type']);
		if (!$imagecreate) return false;
		$imgwidth = $minitemp['width'];
		$imgheight = $minitemp['height'];
		
		$srcX = $srcY = $dstX = $dstY =0;
		if (!$isProportion) {
			$dsDivision = $imgheight / $imgwidth;
			$fixDivision = $dstH / $dstW;
			if ($dsDivision > $fixDivision) {
				$tmp = $imgwidth * $fixDivision;
				$srcY = round(($imgheight - $tmp) / 2);
				$imgheight = $tmp;
			} else {
				$tmp = $imgheight / $fixDivision;
				$srcX = round(($imgwidth - $tmp) / 2); 
				$imgwidth = $tmp;
			}
		}
		$thumb = $imagecreate($minitemp['dstW'], $minitemp['dstH']);
		
		if (function_exists('imagecolorallocate') && function_exists('imagecolortransparent')) {
			$black = imagecolorallocate($thumb, 0, 0, 0);
			imagecolortransparent($thumb, $black);
		}
		$imagecopyre($thumb, $minitemp['source'], $dstX, $dstY, $srcX, $srcY, $minitemp['dstW'], $minitemp['dstH'], $imgwidth, $imgheight);
		self::makeImg($minitemp['type'], $thumb, $dstFile);
		imagedestroy($thumb);
		return array('width' => $minitemp['dstW'], 'height' => $minitemp['dstH'], 'type' => $minitemp['type']);
	}

	/**
	 * 给图片制作水印
	 * 
	 * 水印的位置可以为：
	 * <code>
	 * array(0 => '随机位置', 1 => '顶部居左', 2 => '顶部居中', 3 => '顶部居右', 4 => '底部居左', 5 => '底部居中', 6 => '底部居右', 7 => '中心位置')
	 * </code>
	 * 
	 * @param string $source            图片的源文件
	 * @param int|array $waterPos  		水印的位置,可以选择从0-7或是制定开始位置x,y,默认为0，随机位置
	 * @param string $waterImg     		作为水印的图片,默认为空
	 * @param string $waterText    		作为水印的文字,默认为空
	 * @param array  $attribute       	文字水印的属性，只对文字水印有效
	 * <code>
	 *   array(0 => '字体文件'，1 => '系统编码', 2 => '字体颜色'， 3 => '字体大小')
	 * </code>
	 * @param string $waterPct     		水印透明度，从0到100，0完全透明，100完全不透明，默认为50
	 * @param string $waterQuality   	图片质量--jpeg，默认为75
	 * @param string $dstsrc  			目标文件位置，默认为null即不保存
	 * @return boolean
	 */
	public static function makeWatermark($source, $waterPos = 0, $waterImg = '', $waterText = '', $attribute = '', $waterPct = 50, $waterQuality = 75, $dstsrc = null) {
		$sourcedb = $waterdb = array();
		if (false === ($sourcedb = self::getImgInfo($source))) return false;
		if (!$waterImg && !$waterText) return false;
		imagealphablending($sourcedb['source'], true);
		if ($waterImg) {
			$waterdb = self::getImgInfo($waterImg);
			list($wX, $wY) = self::getWaterPos($waterPos, $sourcedb, $waterdb, 1);
			if ($waterdb['type'] == 'png') {
				$tmp = imagecreatetruecolor($sourcedb['width'], $sourcedb['height']);
				imagecopy($tmp, $sourcedb['source'], 0, 0, 0, 0, $sourcedb['width'], $sourcedb['height']);
				imagecopy($tmp, $waterdb['source'], $wX, $wY, 0, 0, $waterdb['width'], $waterdb['height']);
				$sourcedb['source'] = $tmp;
			} else {
				imagecopymerge($sourcedb['source'], $waterdb['source'], $wX, $wY, 0, 0, $waterdb['width'], $waterdb['height'], $waterPct);
			}
		} elseif ($waterText) {
			list($fontFile, $charset, $color, $waterFont) = self::checkAttribute($attribute);
			empty($waterFont) && $waterFont = 12;
			$temp = imagettfbbox($waterFont, 0, $fontFile, $waterText); //取得使用 TrueType 字体的文本的范围
			$waterdb['width'] = $temp[2] - $temp[6];
			$waterdb['height'] = $temp[3] - $temp[7];
			unset($temp);
			list($wX, $wY) = self::getWaterPos($waterPos, $sourcedb, $waterdb, 2);
			if (strlen($color) != 7) return false;
			$R = hexdec(substr($color, 1, 2));
			$G = hexdec(substr($color, 3, 2));
			$B = hexdec(substr($color, 5));
			self::changeCharset($charset) && $waterText = mb_convert_encoding($waterText, 'UTF-8', $charset);
			imagettftext($sourcedb['source'], $waterFont, 0, $wX, $wY, imagecolorallocate($sourcedb['source'], $R, $G, $B), $fontFile, $waterText);
		}
		$dstsrc && $source = $dstsrc;
		self::makeImg($sourcedb['type'], $sourcedb['source'], $source, $waterQuality);
		isset($waterdb['source']) && imagedestroy($waterdb['source']);
		imagedestroy($sourcedb['source']);
		return true;
	}
	
	/**
	 * 文字水印的属性设置过滤
	 * 
	 * 返回为：
	 * <code>
	 *   array(0 => '字体文件'，1 => '系统编码', 2 => '字体颜色'， 3 => '字体大小')
	 * </code>
	 * @param array $attribute 设置的属性
	 * @return array
	 */
	private static function checkAttribute($attribute) {
		$attribute = is_string($attribute) ? array($attribute) : $attribute;
		if (!isset($attribute[1]) || !$attribute[1]) $attribute[1] = 'UTF-8';
		if (!isset($attribute[2]) || !$attribute[2]) $attribute[2] = '#FF0000';
		if (!isset($attribute[3]) || !$attribute[3]) $attribute[3] = 12;
		return $attribute;
	}
	
	/**
	 * 判断是否需要转编码
	 * 
	 * 判断依据为，编码格式为utf-8
	 * 
	 * @param string $charset 编码方式
	 * @return boolean
	 */
	private static function changeCharset($charset) {
		$charset = strtolower($charset);
	    return !in_array($charset, array('utf8', 'utf-8'));
	}
	
	/**
	 * 获得打水印的位置
	 * 
	 * 如果传入的是数组，则两个元素分别为水印的宽度x和高度y
	 * 
	 * @param int|array $pos 获得水印的位置
	 * @param array $sourcedb 原图片的信息
	 * @param array $waterdb  水印图片的信息
	 * @param int $markType  水印类型，1为图片水印，2为文字水印
	 * @return array
	 */
	private static function getWaterPos($waterPos, $sourcedb, $waterdb, $markType) {
		if (is_array($waterPos)) return $waterPos;
		$wX = $wY = 0;
		switch (intval($waterPos)) {
			case 0 :
				$wX = rand(0, ($sourcedb['width'] - $waterdb['width']));
				$wY = $markType == 1 ? rand(0, ($sourcedb['height'] - $waterdb['height'])) : rand($waterdb['height'], $sourcedb['height']);	
				break;
			case 1 :
				$wX = 5;
				$wY = $markType == 1 ? 5 : $waterdb['height'];
				break;
			case 2:
				$wX = ($sourcedb['width'] - $waterdb['width']) / 2;
				$wY = $markType == 1 ? 5 : $waterdb['height'];
				break;
			case 3:
				$wX = $sourcedb['width'] - $waterdb['width'] - 5;
				$wY = $markType == 1 ? 5 : $waterdb['height'];
				break;
			case 4:
				$wX = 5;
				$wY = $markType == 1 ? $sourcedb['height'] - $waterdb['height'] - 5 : $sourcedb['height'] - 5;
				break;
			case 5:
				$wX = ($sourcedb['width'] - $waterdb['width']) / 2;
				$wY = $markType == 1 ? $sourcedb['height'] - $waterdb['height'] - 5 : $sourcedb['height'] - 5;
				break;
			case 6:
				$wX = $sourcedb['width'] - $waterdb['width'] - 5;
				$wY = $markType == 1 ? $sourcedb['height'] - $waterdb['height'] - 5 : $sourcedb['height'] - 5;
				break;
			default:
				$wX = ($sourcedb['width'] - $waterdb['width']) / 2;
				$wY = $markType == 1 ? ($sourcedb['height'] - $waterdb['height']) / 2 : ($sourcedb['height'] + $waterdb['height']) / 2;
				break;
		}
		return array($wX, $wY);
	}
	
	/**
	 * 获得略缩图的信息
	 * 
	 * @param string $srcFile			源文件
	 * @param int $dstW					目标文件的宽度
	 * @param int $dstH					目标文件的高度
	 * @param boolean $isProportion		是否定比略缩
	 * @return array|boolean    
	 */
	private static function getThumbInfo($srcFile, $dstW, $dstH, $isProportion= FALSE) {
		if (false === ($imgdata = self::getImgInfo($srcFile))) return false;
		if ($imgdata['width'] <= $dstW && $imgdata['height'] <= $dstH) return false;
	
		$imgdata['dstW'] = $dstW;
		$imgdata['dstH'] = $dstH;
		if (empty($dstW) && $dstH > 0 && $imgdata['height'] > $dstH) {
			$imgdata['dstW'] = !$isProportion ? $dstH : round($dstH / $imgdata['height'] * $imgdata['width']);
		} elseif (empty($dstH) && $dstW > 0 && $imgdata['width'] > $dstW) {
			$imgdata['dstH'] = !$isProportion ? $dstW : round($dstW / $imgdata['width'] * $imgdata['height']);
		} elseif ($dstW > 0 && $dstH > 0) {
			if (($imgdata['width'] / $dstW) < ($imgdata['height'] / $dstH)) {
				$imgdata['dstW'] = !$isProportion ? $dstW : round($dstH / $imgdata['height'] * $imgdata['width']);
			}
			if (($imgdata['width'] / $dstW) > ($imgdata['height'] / $dstH)) {
				$imgdata['dstH'] = !$isProportion ? $dstH : round($dstW / $imgdata['width'] * $imgdata['height']);
			}
		} else {
			$imgdata = false;
		}
		return $imgdata;
	}
	
	/**
	 * 获得图片的信息,返回图片的源及图片的高度和宽度
	 * 
	 * @param string $srcFile	图像地址
	 * @return array|boolean
	 */
	public static function getImgInfo($srcFile) {
		if (false === ($imgdata = self::getImgSize($srcFile))) return false;
		$imgdata['type'] = self::getTypes($imgdata['type']);
		if (empty($imgdata) || !function_exists('imagecreatefrom' . $imgdata['type'])) return false;
		$imagecreatefromtype = 'imagecreatefrom' . $imgdata['type'];
		$imgdata['source'] = $imagecreatefromtype($srcFile);
		!$imgdata['width'] && $imgdata['width'] = imagesx($imgdata['source']);
		!$imgdata['height'] && $imgdata['height'] = imagesy($imgdata['source']);
		return $imgdata;
	}
	
	/**
	 * 获得图片的类型及宽高
	 * 
	 * <pre>
	 * 图片type：
	 * 1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，9 = JPC，10 = JP2，
	 * 11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
	 * </pre>、
	 * 
	 * @param string $srcFile	图像地址
	 * @param string $srcExt	图像后缀，默认为null则将会从图片地址中分析获取
	 * @return array|boolean	返回图像的类型及高度和宽度
	 */
	private static function getImgSize($srcFile, $srcExt = null) {
		empty($srcExt) && $srcExt = strtolower(substr(strrchr($srcFile, '.'), 1));
		$srcdata = array();
		$exts = array('jpg', 'jpeg', 'jpe', 'jfif');
		in_array($srcExt, $exts) &&  $srcdata['type'] = 2;
		if (false === ($info = getimagesize($srcFile))) return false;
		list($srcdata['width'], $srcdata['height'], $srcdata['type']) = $info;
		if (!$srcdata['type'] || ($srcdata['type'] == 1 && in_array($srcExt, $exts)))  return false;
		return $srcdata;
	}
	
	/**
	 * 获得创建图像的方法
	 * 
	 * @param string $imagetype		图片类型
	 * @return array
	 */
	private static function getImgcreate($imagetype) {
		if ($imagetype != 'gif' && function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled')) {
			return array('imagecreatetruecolor', 'imagecopyresampled');
		}
		if (function_exists('imagecreate') && function_exists('imagecopyresized')) {
			return array('imagecreate', 'imagecopyresized');
		}
		return array('', '');
	}
	
	/**
	 * 创建图像
	 * 
	 * @param string $type		图像类型
	 * @param resource $image	图像源
	 * @param string $filename	图像保存名字
	 * @param int $quality 		创建jpeg的时候用到，默认为75
	 * @return boolean
	 */
	private static function makeImg($type, $image, $filename, $quality = '75') {
		$makeimage = 'image' . $type;
		if (!function_exists($makeimage)) return false;
		if ($type == 'jpeg') {
			$makeimage($image, $filename, $quality);
		} else {
			$makeimage($image, $filename);
		}
		return true;
	}
	
	/**
	 * 图片的对应类型
	 * 
	 * @param int $id	图片类型ID
	 * @return string
	 */
	private static function getTypes($id) {
		$imageTypes = array(1 => 'gif', 2 => 'jpeg', '3' => 'png', 6 => 'bmp');
		return isset($imageTypes[$id]) ? $imageTypes[$id] : '';
	}
}