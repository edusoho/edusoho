<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:ubb.config.PwUbbCodeConvertConfig');

/**
 * ubb转换
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwUbbCode.php 28913 2013-05-30 05:28:03Z taishici $
 * @package lib.utility
 */

class PwUbbCode {

	protected static $_cvtimes = -1;
	protected static $_code = array();
	protected static $_level = 0;
	protected static $_num = 0;
	protected static $_playerId = 0;
	protected static $_emotion = null;

	/**
	 * 转换段落
	 *
	 * @param string $message 源内容
	 * @return string 转化后的内容
	 */
	public static function convertParagraph($message) {
		if (($pos = strpos($message,"[paragraph]")) !== false && $pos < 10) {
			$message = str_replace('[paragraph]', '', $message);
			$tmplist = explode('<br />', $message);
			$message = '<p style="text-indent: 2em;">' . implode('</p><p style="text-indent: 2em;">', $tmplist) . '</p>';
		}
		return $message;
	}

	/**
	 * 转换同名ubb标签到html
	 *
	 * @param string $message 源内容
	 * @param mixed $tag 要转化的标签 <例: 1.单个 string u/b/ 2.多个 array('u','b')>
	 * @return string 转化后的内容
	 */
	public static function convertTag($message, $tag) {
		is_array($tag) || $tag = array($tag);
		foreach ($tag as $v) {
			$message = str_replace(array("[$v]", "[/$v]"), array("<$v>", "</$v>"), $message);
		}
		return $message;
	}

	/**
	 * 转化hr标签
	 *
	 * @param string $message 内容
	 * @return string
	 */
	public static function convertHr($message) {
		return str_replace('[hr]', '<hr />', $message);
	}

	/**
	 * 转化list标签
	 *
	 * @param string $message 内容
	 * @return string
	 */
	public static function convertList($message) {
		$message = preg_replace('/\[list=([aA1]?)\](.+?)\[\/list\]/is', "<ol type=\"\\1\">\\2</ol>", $message);
		return str_replace(
			array('[list]', '[li]', '[/li]', '[/list]'),
			array('<ul>', '<li>', '</li>', '</ul>'),
			$message
		);
	}

	/**
	 * 转化font标签
	 *
	 * @param string $message 内容
	 * @return string
	 */
	public static function convertFont($message) {
		$message = preg_replace("/\[font=([^\[\(&\\;]+?)\]/is", "<font face=\"\\1 \">", $message);
		return str_replace('[/font]', '</font>', $message);
	}

	/**
	 * 转化color标签
	 *
	 * @param string $message 内容
	 * @return string
	 */
	public static function convertColor($message) {
		$message = preg_replace("/\[color=([#0-9a-z]{1,15})\]/is", "<span style=\"color:\\1 \">", $message);
		return str_replace('[/color]', '</span>', $message);
	}

	/**
	 * 转化backcolor标签
	 *
	 * @param string $message 内容
	 * @return string
	 */
	public static function convertBackColor($message) {
		$message = preg_replace("/\[backcolor=([#0-9a-z]{1,10})\]/is", "<span style=\"background-color:\\1 \">", $message);
		return str_replace('[/backcolor]', '</span>', $message);
	}

	/**
	 * 转化size标签
	 *
	 * @param string $message 内容
	 * @param int $maxSize 最大字体限制 <0.不限制>
	 * @return string
	 */
	public static function convertSize($message, $maxSize = 0) {
		$message = preg_replace("/\[size=(\d+)\]/eis", "self::_size('\\1','$maxSize')", $message);
		return str_replace('[/size]', '</font>', $message);
	}

	protected static function _size($size, $maxSize) {
		$maxSize && $size = min($size, $maxSize);
		return "<font size=\"$size\">";
	}

	/**
	 * 转化email标签
	 *
	 * @param string $message 内容
	 * @return string
	 */
	public static function convertEmail($message) {
		return preg_replace(
			array("/\[email=([^\[]*)\]([^\[]*)\[\/email\]/is", "/\[email\]([^\[]*)\[\/email\]/is"),
			array("<a href=\"mailto:\\1 \">\\2</a>", "<a href=\"mailto:\\1 \">\\1</a>"),
			$message
		);
	}

	/**
	 * 转化align标签
	 *
	 * @param string $message 内容
	 * @return string
	 */
	public static function convertAlign($message) {
		$message = preg_replace("/\[align=(left|center|right|justify)\]/is", "<div align=\"\\1\">", $message);
		return str_replace('[/align]', '</div>', $message);
	}

	/**
	 * 转化glow标签
	 *
	 * @param string $message 内容
	 * @return string
	 */
	public static function convertGlow($message) {
		return preg_replace("/\[glow=(\d+)\,([0-9a-zA-Z]+?)\,(\d+)\](.+?)\[\/glow\]/is", "<div style=\"width:\\1px;filter:glow(color=\\2,strength=\\3);\">\\4</div>", $message);
	}

	/**
	 * 转化table标签
	 *
	 * @param string $message 内容
	 * @param int $max 嵌套时，最大解析层级
	 * @return string
	 */
	public static function convertTable($message, $max = 0) {
		$t = 0;
		while (self::hasTag($message, 'table')) {
			$message = preg_replace('/\[table(?:=(\d{1,4}(?:%|px)?)(?:,(#\w{6})?)?(?:,(#\w{6})?)?(?:,(\d+))?(?:,(\d+))?(?:,(left|center|right))?)?\](?!.*(\[table))(.*?)\[\/table\]/eis', "self::createTable('\\8','\\1','\\2','\\3','\\4','\\5', '\\6')", $message);
			if (++$t > $max) break;
		}
		return $message;
	}

	/**
	 * 解析表情
	 *
	 * @param string $message
	 * @return string
	 */
	public static function parseEmotion($message) {
		$message = preg_replace("/\[s:(.+?)\]/eis","self::createEmotion('\\1')", $message, self::$_cvtimes);
		return $message;
	}

	/**
	 * 解析附件
	 *
	 * @param string $message
	 * @return string
	 */
	public static function parseAttachment($message, $config) {
		return $message = preg_replace('/\[(attachment|p_w_upload|p_w_picpath)=(\d+)\]/eis', "self::createAttachment('\\2', \$config)", $message);
	}

	/**
	 * 转化img标签
	 *
	 * @param string $message 内容
	 * @param int $convertStatus 解析程度
	 * @param int $maxWidth 最大宽度限制
	 * @param int $maxHeight 最大高度限制
	 * @param bool $isLazy 是否输出图片懒加载格式
	 * @return string
	 */
	public static function parseImg($message, $convertStatus = 1, $maxWidth = 0, $maxHeight = 0, $isLazy = false) {
		if ($convertStatus) {
			return preg_replace("/\[img\]([^\<\(\r\n\"']+?)\[\/img\]/eis", "self::createImg('\\1', '$maxWidth', '$maxHeight', '', '$isLazy')", $message, self::$_cvtimes);
		}
		return preg_replace("/\[img\]([^\<\(\r\n\"']+?)\[\/img\]/eis", "self::createImgLink('\\1')", $message, self::$_cvtimes);
	}

	/**
	 * 转化url标签
	 *
	 * @param string $message 内容
	 * @param int $checkurl
	 * @return string
	 */
	public static function parseUrl($message, $checkurl = 0) {
		$searcharray = array(
			"/\[url=((https?|ftp|gopher|news|telnet|mms|rtsp|thunder|ed2k)?[^\[\s]+?)(\,(1)\/?)?\](.+?)\[\/url\]/eis",
			"/\[url\]((https?|ftp|gopher|news|telnet|mms|rtsp|thunder|ed2k)?[^\[\s]+?)\[\/url\]/eis"
		);
		$replacearray = array(
			"self::createUrl('\\1', '\\5', '\\2', '\\4', '$checkurl')",
			"self::createUrl('\\1', '\\1', '\\2', '0', '$checkurl')"
		);
		return preg_replace($searcharray, $replacearray, $message);
	}

	/**
	 * 转化code标签
	 *
	 * @param string $message 内容
	 * @return string
	 */
	public static function parseCode($message) {
		return preg_replace("/\[code(\s*brush\:(.+?)\;toolbar\:(true|false)\;)?\](.+?)\[\/code\]/eis", "self::createCode('\\4', '\\2', '\\3')", $message, self::$_cvtimes);
	}

	/**
	 * 转化post标签
	 *
	 * @param string $message 内容
	 * @param object $config ubb转换配置
	 * @return string
	 */
	public static function parsePost($message, $config) {
		return preg_replace("/\[post\](.+?)\[\/post\]/eis","self::createPost('\\1', \$config)", $message);
	}

	/**
	 * 转化hide标签
	 *
	 * @param string $message 内容
	 * @param object $config ubb转换配置
	 * @return string
	 */
	public static function parseHide($message, $config) {
		return preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/eis","self::createHide('\\1', '\\2', \$config)", $message);
	}

	/**
	 * 转化sell标签
	 *
	 * @param string $message 内容
	 * @param object $config ubb转换配置
	 * @return string
	 */
	public static function parseSell($message, $config) {
		return preg_replace("/\[sell=(\d+)(\,(\d+))?\](.+?)\[\/sell\]/eis", "self::createSell('\\1', '\\3', '\\4', \$config)", $message);
	}

	/**
	 * 转化quote标签
	 *
	 * @param string $message 内容
	 * @return string
	 */
	public static function parseQuote($message) {
		return preg_replace("/\[quote(=(.+?)\,(\d+))?\](.*?)\[\/quote\]/eis","self::createQoute('\\4', '\\2', '\\3')", $message);
		/** 盖楼
		while (self::hasTag($message, 'quote')) {
			$message = preg_replace("/\[quote(=([^,]+?)\,(\d+))?\](?!.*?\[quote.*?)(.*?)\[\/quote\]/eis", "self::createQoute('\\4', '\\2', '\\3')", $message);
		}
		return $message;
		**/
	}

	/**
	 * 转化flash标签
	 *
	 * @param string $message 内容
	 * @param int $convertStatus 解析程度
	 * @return string
	 */
	public static function parseFlash($message, $convertStatus = 1) {
		if ($convertStatus) {
			return preg_replace("/\[flash(=(\d+?)\,(\d+?)(\,(0|1))?)?\]([^\[\<\(\r\n\"']+?)\[\/flash\]/eis", "self::createPlayer('\\6','\\2','\\3','\\5','video')", $message, self::$_cvtimes);
		}
		return preg_replace("/\[flash(=(\d+?)\,(\d+?)(\,(0|1))?)?\]([^\[\<\(\r\n\"']+?)\[\/flash\]/eis", "self::createFlashLink('\\6')", $message, self::$_cvtimes);
	}

	/**
	 * 转化 wmv|mp3|rm 等视频媒体标签
	 *
	 * @param string $message 内容
	 * @param int $convertStatus 解析程度
	 * @return string
	 */
	public static function parseMedia($message, $convertStatus = 1) {
		if ($convertStatus) {
			return preg_replace(
				array(
					"/\[(wmv|mp3)(=(0|1))?\]([^\<\(\r\n\"']+?)\[\/\\1\]/eis",
					"/\[(wmv|rm)(=([0-9]{1,3})\,([0-9]{1,3})\,(0|1))?\]([^\<\(\r\n\"']+?)\[\/\\1\]/eis"
				),
				array(
					"self::createPlayer('\\4','314','53','\\3','audio')",
					"self::createPlayer('\\6','\\3','\\4','\\5','video')"
				),
				$message,
				self::$_cvtimes
			);
		}
		return preg_replace(
			array(
				"/\[(mp3|wmv)(?:=[01]{1})?\]([^\<\r\n\"']+?)\[\/\\1\]/eis",
				"/\[(wmv|rm)(?:=[0-9]{1,3}\,[0-9]{1,3}\,[01]{1})?\]([^\<\r\n\"']+?)\[\/\\1\]/eis",
			),
			"self::createMediaLink('\\2')",
			$message,
			self::$_cvtimes
		);
	}

	public static function parseRemind($message, $remindUser) {
		return preg_replace('/@([\x7f-\xff\dA-Za-z\.\_]+)(?=\s?)/ie', "self::createRemind('\\1', \$remindUser)", $message);
	}

	/**
	 * 转化iframe标签
	 *
	 * @param string $message 内容
	 * @param int $convertStatus 解析程度
	 * @return string
	 */
	public static function parseIframe($message, $convertStatus = 1) {
		return preg_replace("/\[iframe\]([^\[\<\(\r\n\"']+?)\[\/iframe\]/eis", "self::createIframe('\\1', \$convertStatus)", $message, self::$_cvtimes);
	}

	protected static function _init() {
		self::$_code = array();
		self::$_num = 0;
	}

	protected static function _startParse() {
		self::$_code[++self::$_level] = array();
	}

	protected static function _pushCode($code) {
		if (self::$_level === 0) {
			return $code;
		}
		$length = array_push(self::$_code[self::$_level], $code);
		return "<\twind_code_" . self::$_level . '_' . ($length - 1) . "\t>";
	}

	protected static function _convertCode($message) {
		if (self::$_code[self::$_level]) {
			krsort(self::$_code[self::$_level]);
			foreach (self::$_code[self::$_level] as $key => $code) {
				$message = str_replace("<\twind_code_" . self::$_level. "_$key\t>", $code, $message);
			}
		}
		self::$_level--;
		return $message;
	}

	/**
	 * 检测内容中是否包含标签
	 *
	 * @param string $message 内容
	 * @param string $tag 标签
	 * @return bool
	 */
	public static function hasTag($message, $tag) {
		$startTag = '[' . $tag;
		$endTag = '[/' . $tag . ']';
		if (strpos($message, $startTag) !== false && strpos($message, $endTag) !== false) {
			return true;
		}
		return false;
	}

	/**
	 * 转化ubb标签
	 *
	 * @param string $message
	 * @param object $config ubb转换配置
	 * @return string
	 */
	public static function convert($message, PwUbbCodeConvertConfig $config = null) {
		is_null($config) && $config = new PwUbbCodeConvertConfig();
		self::_init();
		self::_startParse();
		self::$_cvtimes = $config->cvtimes;

		self::hasTag($message, 'code') && $message = self::parseCode($message);

		$message = PwSimpleHook::getInstance('PwUbbCode_convert')->runWithFilters($message);
		$message = self::convertTag($message, array('u', 'b', 'i', 'sub', 'sup', 'strike', 'blockquote'));
		$message = self::convertHr($message);
		$message = self::convertList($message);
		$message = self::convertFont($message);
		$message = self::convertColor($message);
		$message = self::convertBackColor($message);
		$message = self::convertSize($message, $config->maxSize);
		$message = self::convertEmail($message);
		$message = self::convertAlign($message);
		$message = self::convertGlow($message);

		self::_startParse();
		strpos($message, '[s:') !== false && $message = self::parseEmotion($message);
		$message = self::parseAttachment($message, $config);
		self::hasTag($message, 'img') && $message = self::parseImg($message, $config->isConverImg, $config->imgWidth, $config->imgHeight, $config->imgLazy);
		self::hasTag($message, 'url') && $message = self::parseUrl($message);
		self::hasTag($message, 'flash') && $message = self::parseFlash($message, $config->isConvertFlash);
		$config->remindUser && $message = self::parseRemind($message, $config->remindUser);
		$config->isConvertMedia && $message = self::parseMedia($message, $config->isConvertMedia == 2);
		$config->isConvertIframe && self::hasTag($message, 'iframe') && $message = self::parseIframe($message, $config->isConvertIframe == 2);
		$config->isConvertPost && self::hasTag($message, 'post') && $message = self::parsePost($message, $config);
		$config->isConvertHide && self::hasTag($message, 'hide') && $message = self::parseHide($message, $config);
		$config->isConvertSell && self::hasTag($message, 'sell') && $message = self::parseSell($message, $config);
		self::hasTag($message, 'quote') && $message = self::parseQuote($message);
		$config->isConvertTable && $message = self::convertTable($message, $config->isConvertTable);
		$message = self::convertParagraph($message);
		$message = self::_convertCode($message);

		$message = self::_convertCode($message);
		self::$_cvtimes = -1;
		return $message;
	}

	/**
	 * 自动转化url到ubb标签
	 *
	 * @param string $message
	 * @param bool $hasCode 是否处理code标签
	 * @return string
	 */
	public static function autoUrl($message, $hasCode = false) {
		if ($hasCode) {
			self::_init();
			self::_startParse();
			self::hasTag($message, 'code') && $message = preg_replace("/\[code.*?\].+?\[\/code\]/eis", "self::srcCode('\\0')", $message, self::$_cvtimes);
		}
		$message = preg_replace(
			"/(?<![\]a-z0-9-=\"'(\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│\|]+)/i",
			"[url]\\1\\3[/url]",
			$message
		);
		if ($hasCode) {
			$message = self::_convertCode($message);
		}
		return $message;
	}

	public static function srcCode($str) {
		return self::_pushCode(str_replace('\"', '"', $str));
	}

	/**
	 * 生成表情html标签
	 *
	 * @param int $key 表情序号
	 * @return string 表情html
	 */
	public static function createEmotion($key) {
		is_null(self::$_emotion) && self::$_emotion = Wekit::cache()->get('all_emotions');
		isset(self::$_emotion['name'][$key]) && $key = self::$_emotion['name'][$key];
		$emotion = isset(self::$_emotion['emotion'][$key]) ? self::$_emotion['emotion'][$key] : current(self::$_emotion['emotion']);
		$html = "<img src=\"" . Wekit::url()->images . "/emotion/" . $emotion['emotion_folder'] . '/' . $emotion['emotion_icon'] . "\" />";
		return self::_pushCode($html);
	}

	/**
	 * 生成附件html标签
	 *
	 * @param int $aid 附件id
	 * @return string 附件html
	 */
	public static function createAttachment($aid, $config) {
		return self::_pushCode($config->getAttachHtml($aid));
	}

	/**
	 * 生成img标签
	 *
	 * @param string $path 图片地址
	 * @param int $maxWidth 最大宽度限制
	 * @param int $maxHeight 最大高度限制
	 * @param string $original 原图地址
	 * @param bool $isLazy 是否输出图片懒加载格式
	 * @return string 图片html
	 */
	public static function createImg($path, $maxWidth = 0, $maxHeight = 0, $original = '', $isLazy = false) {
        $path = self::escapeUrl($path); //by taishici
		if ($isLazy) {
			$html = '<img class="J_post_img J_lazy" data-original="' . $path . '" src="' .  Wekit::url()->images . '/blank.gif" border="0"';
		} else {
			$html = '<img class="J_post_img" src="' . $path . '" border="0"';
		}
		if ($maxWidth && $maxHeight) {
			$html .= " onload=\"if(this.offsetWidth>$maxWidth || this.offsetHeight>$maxHeight){if(this.offsetWidth/$maxWidth > this.offsetHeight/$maxHeight){this.width=$maxWidth;}else{this.height=$maxHeight;}}\"";
			$html .= " style=\"max-width:{$maxWidth}px;max-height:{$maxHeight}px;\"";
		} elseif ($maxWidth) {
			$html .= " onload=\"if(this.offsetWidth>$maxWidth)this.width=$maxWidth;\"";
			$html .= " style=\"max-width:{$maxWidth}px;\"";
		} elseif ($maxHeight) {
			$html .= " onload=\"if(this.offsetHeight>$maxHeight)this.height=$maxHeight;\"";
			$html .= " style=\"max-height:{$maxHeight}px;\"";
		}
		if ($original) {
			$html .= " title=\"点击查看原图\" onclick=\"if(this.parentNode.tagName!='A') window.open('$original');\"";
		} else {
			$html .= ' onclick="if(this.parentNode.tagName!=\'A\'&&this.width>screen.width-461) window.open(this.src);"';
		}
		$html .= ' />';
		return self::_pushCode($html);
	}

	/**
	 * 生成 图片 链接
	 *
	 * @param string $path
	 * @return string
	 */
	public static function createImgLink($path) {
        $path = self::escapeUrl($path); //by taishici
		$html = "<img src=\"" . Wekit::url()->images . "/wind/file/img.gif\" align=\"absbottom\"> <a target=\"_blank\" href=\"$path \">$path</a>";
		return self::_pushCode($html);
	}

	/**
	 * 生成a标签
	 *
	 * @param string $url 链接地址
	 * @param string $name 链接内容
	 * @param string $protocol 链接协议头
	 * @param int $isdownload 链接是否为下载样式
	 * @param int $checkurl
	 * @return string
	 */
	public static function createUrl($url, $name, $protocol, $isdownload = 0, $checkurl = 0) {
		!$protocol && $url = 'http://' . $url;
		$attributes = '';
		$isdownload && $attributes .= ' class="down"';
		$html = "<a href=\"$url \" target=\"_blank\"{$attributes}>$name</a>";
		return self::_pushCode($html);
	}

	/**
	 * 生成code标签内容
	 *
	 * @param string $str 内容
	 * @param string $brush 代码语法
	 * @param string $toolbar 是否有工具栏
	 * @return string
	 */
	public static function createCode($str, $brush, $toolbar) {
		!$brush && $brush = 'text';
		!$toolbar && $toolbar = 'false';
		$str = str_replace(array("[attachment=", '\\"'), array("&#91;attachment=", '"'), trim($str));
		$str = preg_replace('/^(<br \/>)?(.+?)(<br \/>)$/','\\2', $str);
		$str = str_replace('<br />', "\n", $str);
		$html = "<pre data-role=\"code\" class=\"brush: $brush;toolbar:$toolbar;\">$str</pre>";
		return self::_pushCode($html);
	}

	/**
	 * 生成post标签内容
	 *
	 * @param stirng $str 内容
	 * @param object $config ubb转换配置
	 * @return string
	 */
	public static function createPost($str, $config) {
		if ($ispost = $config->isPost()) {
			$html = "<div class=\"content_hidden\"><h6>本部分设定了隐藏," . ($ispost > 1 ? '' : '您已回复过了,') . "以下是隐藏的内容</h6><div>" . str_replace('\\"', '"', $str) . "</div></div>";
		} else {
			$html = "<div class=\"content_hidden\" id=\"J_need_reply\">本部分内容设定了隐藏,需要回复后才能看到</div>";
		}
		return $html;
	}

	/**
	 * 生成hide标签内容
	 *
	 * @param int $cost 需要的积分
	 * @param stirng $str 隐藏的内容
	 * @param object $config ubb转换配置
	 * @return string
	 */
	public static function createHide($cost, $str, $config) {
		if ($config->isLogin()) {
			$creditBo = PwCreditBo::getInstance();
			list($cost, $credit) = explode(',', $cost);
			isset($creditBo->cType[$credit]) || $credit = key($creditBo->cType);
			$cName = $creditBo->cType[$credit];
			if ($config->checkCredit($cost, $credit)) {
				$html = "<h6 class=\"quote\" style=\"padding:0;margin:0;\"><span class=\"s2 f12 fn\">"
					. "该帖是加密帖,需要{$cost}{$cName}及以上的积分才能浏览以下内容:"
					. "</span></h6><blockquote class=\"blockquote\" style=\"margin:10px 0;\">"
					. str_replace('\\"','"',$str)
					. "</blockquote>";
			} else {
				$html = "<blockquote class=\"blockquote\" style=\"margin:10px 0;\">"
					. "本部分内容设定了加密,需要{$cost}{$cName}以上的积分才能浏览"
					. "</blockquote>";
			}
		} else {
			$_url = WindUrlHelper::createUrl('u/login/run');
			$html = "<blockquote class=\"blockquote\" style=\"margin:10px 0;\">"
				. '对不起!您没有登录,请先<a href="' . $_url . '" class="J_qlogin_trigger"><font color="red">登录论坛</font></a>.'
				. "</blockquote>";
		}
		return $html;
	}

	/**
	 * 生成sell标签内容
	 *
	 * @param int $cost 需要的积分
	 * @param stirng $str 隐藏的内容
	 * @param object $config ubb转换配置
	 * @return string
	 */
	public static function createSell($cost, $credit, $str, $config) {
		Wind::import('SRV:credit.bo.PwCreditBo');
		$creditBo = PwCreditBo::getInstance();
		$credit = isset($creditBo->cType[$credit]) ? $credit : key($creditBo->cType);
		$cName = $creditBo->cType[$credit];
		$html = '';
		if (self::$_num++ == 0) {
			list($recordUrl, $buyUrl, $sellCount) = $config->getSellInfo();
			$html .= "<div class=\"content_sell\" id=\"J_content_sell\"><h6><span class=\"mr10\">"
					. "此帖售价 <span id=\"J_buy_price\">$cost</span> <span id=\"J_buy_util\">$cName</span>,已有 <span id=\"J_buy_count\">$sellCount</span> 人购买"
					. "</span> "
					. "<a href=\"$recordUrl\" title=\"查看记录\" class=\"mr10 fn J_buy_record\" data-buycount=\"\">[记录]</a>";
			if (!$config->isAuthor() && !$config->isBuy()) {
				$userCredit = $config->getUserCredit($credit);
				$html .= " <a href=\"$buyUrl\" title=\"购买\" class=\"fn J_post_buy J_qlogin_trigger\" data-credit=\"$userCredit\" data-price=\"$cost\" data-util=\"$cName\" data-role=\"post\">[购买]</a>";
			}
			$html .= "</h6></div>";
		}
		if ($config->isBuy()) {
			$html .= "<div class=\"content_sell\">"
					. str_replace('\\"', '"', $str)
					. "</div>";
		} else {
			$html .= "<div class=\"content_sell\">"
					. "<h6>此段为出售的内容，购买后显示</h6>"
					. "</div>";
		}
		return $html;
	}

	/**
	 * 生成quote标签内容
	 *
	 * @param stirng $str 引用的内容
	 * @return string
	 */
	public static function createQoute($str, $username = '', $rpid = 0) {
		$str = str_replace('\\"', '"', $str);
		$username && $str = "<span class=\"fl\"><a href=" . WindUrlHelper::createUrl('space/index/run', array('username' => $username)) . ">" . $username . '</a>：' . $str . '</span>';
		$rpid && $str .= '<a href="' . WindUrlHelper::createUrl('bbs/read/jump', array('pid' => $rpid)) . '" class="return">回到原帖</a>';
		$html = "<blockquote class=\"blockquote cc\">" . $str . "</blockquote>";
		return self::_pushCode($html);
	}

	/**
	 * 生成播放器
	 *
	 * @param stirng $url url地址
	 * @param int $width 宽度
	 * @param int $height 高度
	 * @param int $auto 是否为自动播放<1.是 2.否>
	 * @param string $type 播放器类型 <可选: audio|video>
	 * @return string
	 */
	public static function createPlayer($url, $width = 0, $height = 0, $auto = 0, $type = 'video') {
        $url = self::escapeUrl($url); //by taishici
		if (!preg_match('/\.(rmvb|rm|wmv|avi|mp3|wma|swf|flv)/i', $url, $match)) {
			$html = "<a href=\"$url \" target=\"_blank\">$url</a>";
		} elseif ($type == 'audio') {
			$html = "<div class=\"J_audio\" data-url=\"$url\" data-autoplay=\"$auto\" data-type=\"{$match[1]}\"></div>";
		} else {
			!$width && $width = 314;
			!$height && $height = 256;
			$html = "<div class=\"J_video\" data-url=\"$url\" data-autoplay=\"1\" data-width=\"$width\" data-height=\"$height\" data-type=\"{$match[1]}\"></div>";
		}
		return self::_pushCode($html);
	}

	/**
	 * 生成 flash 链接
	 *
	 * @param string $url
	 * @return string
	 */
	public static function createFlashLink($url) {
        $url = self::escapeUrl($url); //by taishici
		$html = "<span class=\"posts_icon\"><i class=\"icon_music\"><i></span> <a target=\"_blank\" href=\"$url \">flash: $url</a>";
		return self::_pushCode($html);
	}

	/**
	 * 生成视频链接
	 *
	 * @param string $url
	 * @return string
	 */
	public static function createMediaLink($url) {
        $url = self::escapeUrl($url); //by taishici
		$html = "<span class=\"posts_icon\"><i class=\"icon_music\"><i></span> <a target=\"_blank\" href=\"$url \">$url</a>";
		return self::_pushCode($html);
	}

	public static function createRemind($username, $uArray) {
		return isset($uArray[$username]) ? '<a href="'. WindUrlHelper::createUrl('space/index/run', array('uid' => $uArray[$username])) . '">@' . $username . '</a>' : '@' . $username;
	}

	/**
	 * 生成iframe标签内容
	 *
	 * @param string $url
	 * @param int $convertStatus 解析程度
	 * @return string
	 */
	public static function createIframe($url, $convertStatus) {
        $url = self::escapeUrl($url); //by taishici
		if ($convertStatus) {
			$html = "<iframe src=\"$url\" frameborder=\"0\" allowtransparency=\"true\" scrolling=\"yes\" width=\"97%\" height=\"340\"></iframe>";
		} else {
			$html = "Iframe Close: <a target=\"_blank\" href=\"$url \">$url</a>";
		}
		return self::_pushCode($html);
	}

	/**
	 * 生成table标签内容
	 *
	 * @param string $text
	 * @param int $width 宽度
	 * @param string $bgColor 背景色
	 * @param string $borderColor 边框色
	 * @param int $borderWidth 边框大小
	 * @return string
	 */
	public static function createTable($text, $width = '', $bgColor = '', $borderColor = '', $borderWidth = '', $cellpadding = '', $align = '') {
		if ($width && preg_match('/^(\d{1,3})(%|px)?$/', $width, $matchs)) {
			$unit = $matchs[2] ? $matchs[2] : 'px';
			$width = $unit == 'px' ? min($matchs[1], 600).'px' : min($matchs[1], 100).'%';
		} else {
			$width = '100%';
		}
		$tableStyle = 'width:' . $width;
		$bgColor && $tableStyle .= ';background-color:' . $bgColor;
		$borderWidth && $tableStyle .= ';border-width:' . $borderWidth . 'px;border-style:solid';
		!$borderColor && $borderColor = '#ffffff';
		$tableStyle .= ';border-color:' . $borderColor;
		$tdStyle = ' style="border-color:' . $borderColor . '"';
		$cellpadding || $cellpadding = 0;
		$align || $align = 'left';

		$text = trim(str_replace(array('\\"', '<br />'), array('"', "\n"), $text));
		$text = preg_replace(
			array('/(\[\/td\]\s*)?\[\/tr\]\s*/is', '/\[(tr|\/td)\]\s*\[td(=(\d{1,2}),(\d{1,2})(,(\d{1,3}(\.\d{1,2})?(%|px)?))?)?\]/eis'),
			array('</td></tr>', "self::createTd('\\1','\\3','\\4','\\6','$tdStyle')","<tr><td{$tdStyle}>"),
			$text
		);
		$text = str_replace('[tr]', "<tr><td{$tdStyle}>", $text);
		$text = str_replace("\n", '<br />', $text);

		return self::_pushCode("<table class=\"read_form\" style=\"$tableStyle\" cellspacing=\"0\" cellpadding=\"{$cellpadding}\" align=\"$align\">$text</table>");
	}

	/**
	 * 生成td标签
	 *
	 * @param string $tag 标签 <tr|td>
	 * @param int $col 多列
	 * @param int $row 多行
	 * @param int $width 宽度
	 * @param string $tdStyle 样式
	 * @return string
	 */
	public static function createTd($tag, $col, $row, $width, $tdStyle = '') {
		return ($tag == 'tr' ? '<tr>' : '</td>').(($col && $row) ? "<td colspan=\"$col\" rowspan=\"$row\" width=\"$width\"{$tdStyle}>" : "<td{$tdStyle}>");
	}

    /**
     * 白盒过滤http://以及特殊符号
     *
     * @return string
     */
    public static function escapeUrl($path) {
        if(!(strpos($path, 'http://')===0 || strpos($path, 'https://')===0)) {
            return '';
        }
        $path = str_replace(array("<",">","'","\"",";"), array("%3c","%3e","%27","%22","%3b"),$path);
        return $path;
    }
}