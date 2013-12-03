<?php
 /**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ foxsee@aliyun.com
 * @version $Id: PwBaseCode.php 20486 2012-10-30 07:50:14Z gao.wanggao $ 
 * @package 
 */
 class PwBaseCode {
	/**
	 * 验证码长度
	 * 
	 * @var int
	 */
	public static $verifyLength = 4;
	
	/**
	 * 1.数字 2.字母 3.数字+字母 4,随机加减，5.汉字6.自定义问题 7语音
	 * 
	 * @var int
	 */
	public static $verifyType = 3;
	
	public static $verifyWidth = 240;
	
	public static $verifyHeight = 60;
	
	public static $isRandBackground = false;
	
	public static $isRandGraph = false;
	
	public static $isRandFont = false;
	
	public static $isRandSize = false;
	
	public static $isRandAngle = false;
	
	public static $isRandColor = false;
	
	public static $isRandGif = false;
	
	public static $isRandDistortion = false;
	
	public static $askCode = '';
	
	public static $answerCode = '';
	
	protected static $verifyCode = '';
	
	protected static $path = 'REP:';
	
	public static function getCode() {
		if (in_array(self::$verifyType, array(4,6))) return self::$answerCode;
		return strtolower(self::$verifyCode);
	}
	
	/**
	 * 设置验证码
	 * 
	 * @return void
	 */
	protected static function setRandCode() {
		switch (self::$verifyType) {
			case '1':
		   		$str = '1234567890';
		    	break;
		    case '2':
		   		$str = 'abcdefghjkmnpqrstuvwxyABCDEFGHJKLMNPQRSTUVWXY';
		    	break;
		    case '3':
		    default:
		   		$str = '3456789bcefghjkmpqrtvwxyzBCEFGHJKMPQRTVWXYZ';
		    	break;
		    case '5':
		   		$str = '人之初性本善性相近习相远苟不教性乃迁教之道贵以专昔孟母择邻处子不学断机杼窦燕山有义方教五子名俱扬养段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑';
		   	 	break;
		   	case '4':
		    case '6':
		    	self::$verifyCode = self::_convert(self::$askCode);
		   		return true;
		    case '7': //目前只有这些声音文件
		    	$str = '123456789BCEFGHJKMPQRTVWXYZ';
		    	break;		
		}
	  	$_tmp = Pw::strlen($str)-1;
	    $_num=0;
	    for($i = 0;$i < self::$verifyLength;$i++){
	        $_num = mt_rand(0, $_tmp);
	        $_code = Pw::substrs($str, 1,$_num, false); 
	        self::$verifyCode .=  self::_convert($_code);
	    }
	}
	
 	private static function _convert($text='') {
 		return Pw::convert($text, 'UTF-8');
		/*if ($text !== utf8_decode(utf8_encode($text))) {
			$text = WindConvert::convert($text, 'UTF-8', 'GBK');
		}
		return $text;*/
	}
	
 	/**
	 * 获取验证码背景文件
	 *
	 * @return array
	 */
	protected static function getVerifyBackground() {
		$_files = array();
		$_path = Wind::getRealDir(self::$path.'.bg.');
		$files = WindFolder::read($_path);
		foreach ($files AS $file) {
			if (is_file($_path .$file)) $_files[] = $_path .$file;
		}
		return $_files;
	}
	
	/**
	 * 获取字体列表
	 *
	 * @return array
	 */
	protected static function getFontList() {
		$_path=Wind::getRealDir(self::$path.'.font');
		return WindFolder::read($_path, WindFolder::READ_FILE);
	}
	
	/**
	 * 获取英文字体列表
	 *
	 * @return array
	 */
	protected static function getEnFontList() {
		$_fontList = array();
		$fontList = self::getFontList();
		foreach ($fontList AS $key=>$font) {
			if (strpos($font, 'en_')===0) {
				$_fontList[] = $font;
			}
		}
		return $_fontList ?  $_fontList : array('en_arial.ttf');
	}
	
	/**
	 * 获取中文字体列表
	 *
	 * @return array
	 */
	protected static function getCnFontList() {
		$_fontList =array();
		$fontList = self::getFontList();
		foreach ($fontList AS $key=>$font) {
			if (strpos($font, 'cn_')===0) {
				$_fontList[] = $font;
			}
		}
		return $_fontList;
	}
	
 }
?>