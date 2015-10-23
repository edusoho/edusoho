<?php
Wind::import('WIND:utility.WindFile');
/**
 * 程序打包工具
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindPack.php 3647 2012-06-08 04:14:06Z yishuo $
 * @package utility
 */
class WindPack {
	
	/**
	 * 使用正则打包
	 * 
	 * @var string 
	 */
	const STRIP_SELF = 'stripWhiteSpaceBySelf';
	
	/**
	 * 利用php自身的函数打包
	 * 
	 * @var string 
	 */
	const STRIP_PHP = 'stripWhiteSpaceByPhp';
	
	/**
	 * 通过token方式打包
	 * 
	 * @var string 
	 */
	const STRIP_TOKEN = 'stripWhiteSpaceByToken';
	private $packList = array();
	private $contentInjectionPosition;
	private $contentInjectionCallBack = '';

	/**
	 * 将给出的文件列表进行打包
	 * 
	 * @param mixed $fileList 文件列表
	 * @param string $dst  打包文件的存放位置
	 * @param method $packMethod 打包的方式，默认为stripWhiteSpaceByPhp
	 * @param boolean $compress 打包是否采用压缩的方式，默认为true
	 * @return boolean
	 */
	public function packFromFileList($fileList, $dst, $packMethod = WindPack::STRIP_PHP, $compress = true) {
		if (empty($dst) || empty($fileList)) return false;
		$content = array();
		$this->readContentFromFileList($fileList, $packMethod, $content);
		$replace = $compress ? ' ' : "\n";
		$content = implode($replace, $content);
		$content = $this->callBack($content, $replace);
		$content = $this->stripNR($content, $replace);
		$content = $this->stripPhpIdentify($content, '');
		return WindFile::write($dst, '<?php' . $replace . $content . '?>');
	}

	/**
	 * 通过php自身方式去除指定文件的注释及空白
	 * 
	 * @param string $filename 文件名
	 * @return string
	 */
	public function stripWhiteSpaceByPhp($filename) {
		return php_strip_whitespace($filename);
	}

	/**
	 * 通过正则方式去除指定文件的注释及空白
	 * 
	 * @param string $filename 文件名字
	 * @param boolean $compress 是否采用压缩，默认为true
	 * @return string
	 */
	public function stripWhiteSpaceBySelf($filename, $compress = true) {
		$content = $this->getContentFromFile($filename);
		$content = $this->stripComment($content, '');
		return $this->stripSpace($content, ' ');
	}

	/**
	 * 通过token方式去除指定文件的注释及空白
	 * 
	 * @param string $filename 文件名称
	 * @return string
	 */
	public function stripWhiteSpaceByToken($filename) {
		$content = $this->getContentFromFile($filename);
		$compressContent = '';
		$lastToken = 0;
		foreach (token_get_all($content) as $key => $token) {
			if (is_array($token)) {
				if (in_array($token[0], array(T_COMMENT, T_WHITESPACE, T_DOC_COMMENT))) {
					continue;
				}
				$compressContent .= ' ' . $token[1];
			} else {
				$compressContent .= $token;
			}
			$lastToken = $token[0];
		}
		return $compressContent;
	}

	/**
	 * 从文件列表中取得对应的每个文件的内容 
	 * 
	 * @param mixed $fileList 文件列表
	 * @param method $packMethod  打包方式，默认为stripWhiteSpaceByPhp
	 * @param array $content 保存文件内容，默认为空数组
	 * @return array:
	 */
	public function readContentFromFileList(array $fileList, $packMethod = WindPack::STRIP_PHP, &$content = array()) {
		if (empty($fileList) || false === $this->isValidatePackMethod($packMethod)) return array();
		
		foreach ($fileList as $key => $value) {
			$parents = class_parents($key);
			$_fileList = $this->buildFileList($parents, $fileList);
			$this->readContentFromFileList($_fileList, $packMethod, $content);
			$implements = class_implements($key);
			$_fileList = $this->buildFileList($implements, $fileList);
			$this->readContentFromFileList($_fileList, $packMethod, $content);
			if (in_array($key, $this->packList)) continue;
			if (is_file($value)) {
				$content[] = $this->$packMethod($value);
				$this->packList[] = $key;
			}
		}
	}

	/**
	 * 去除注释
	 * 
	 * @param string $content 要去除的内容
	 * @param mixed $replace 要替换的文本
	 * @return string
	 */
	public function stripComment($content, $replace = '') {
		return preg_replace('/(?:\/\*.*\*\/)*|(?:\/\/[^\r\n]*[\r\n])*/Us', $replace, $content);
	}

	/**
	 * 去除换行
	 * 
	 * @param string $content 要去除的内容
	 * @param mixed $replace 要替换的文本
	 * @return string
	 */
	public function stripNR($content, $replace = array('\n','\r\n','\r')) {
		return preg_replace('/[\n\r]+/', $replace, $content);
	}

	/**
	 * 去除空格符
	 * 
	 * @param string $content 要去除的内容
	 * @param mixed $replace 要替换的文本,默认为空 
	 * @return string
	 */
	public function stripSpace($content, $replace = ' ') {
		return preg_replace('/[ ]+/', $replace, $content);
	}

	/**
	 * 去除php标识
	 * 
	 * @param string $content 需要处理的内容
	 * @param mixed $replace 将php标识替换为该值，默认为空
	 * @return string
	 */
	public function stripPhpIdentify($content, $replace = '') {
		return preg_replace('/(?:<\?(?:php)*)|(\?>)/i', $replace, $content);
	}

	/**
	 * 根据指定规则替换指定内容中相应的内容
	 * 
	 * @param string $content 需要处理的内容
	 * @param string $rule    需要匹配的正则
	 * @param $mixed $replace 用来替换将匹配出来的结果，默认为空
	 * @return string
	 */
	public function stripStrByRule($content, $rule, $replace = '') {
		return preg_replace("/$rule/", $replace, $content);
	}

	/**
	 * 去除多余的文件导入信息
	 * 
	 * @param string $content 需要处理的内容
	 * @param mixed $replace 用来替换将匹配出来的结果，默认为空
	 * @return string
	 */
	public function stripImport($content, $replace = '') {
		$str = preg_match_all('/L[\t ]*::[\t ]*import[\t ]*\([\t ]*[\'\"]([^$][\w\.:]+)[\"\'][\t ]*\)[\t ]*/', $content, 
			$matchs);
		if ($matchs[1]) {
			foreach ($matchs[1] as $key => $value) {
				$name = substr($value, strrpos($value, '.') + 1);
				if (preg_match("/(abstract[\t ]*|class|interface)[\t ]+$name/i", $content)) {
					$strip = str_replace(array('(', ')'), array('\(', '\)'), addslashes($matchs[0][$key])) . '[\t ]*;';
					$content = $this->stripStrByRule($content, $strip, $replace);
				}
			}
		}
		return $content;
	}

	/**
	 * 从文件读取内容
	 *
	 * @param string $filename 文件名
	 * @return string 如果给出的文件不是一个有效文件则返回false
	 */
	public function getContentFromFile($filename) {
		if (is_file($filename)) {
			$content = '';
			$fp = fopen($filename, "r");
			while (!feof($fp)) {
				$line = fgets($fp);
				if (in_array(strlen($line), array(2, 3)) && in_array(ord($line), array(9, 10, 13))) continue;
				$content .= $line;
			}
			fclose($fp);
			return $content;
		}
		return false;
	}

	/**
	 * 构造文件列表
	 *
	 * @param array $list     需要处理的文件列表
	 * @param array $fileList 文件列表
	 * @return array 保存$list中存在于$fileList中的文件列表
	 */
	private function buildFileList(array $list, $fileList) {
		$_temp = array();
		foreach ($list as $fileName) {
			foreach ($fileList as $key => $value) {
				if ($key == $fileName) {
					$_temp[$key] = $value;
					break;
				}
			}
		}
		return $_temp;
	}

	/**
	 * 设置回调
	 * 
	 * @author Qiong Wu
	 * @param array $contentInjectionCallBack 回调函数
	 * @param string $position 调用位置(before|after)默认为before
	 * @return void
	 */
	public function setContentInjectionCallBack($contentInjectionCallBack, $position = 'before') {
		if (!in_array($position, array('before', 'after'))) $position = 'before';
		$this->contentInjectionPosition = $position;
		$this->contentInjectionCallBack = $contentInjectionCallBack;
	}

	/**
	 * 回调函数调用
	 * 
	 * @param string $content 被回调的内容
	 * @param string $replace 替换内容，默认为空
	 * @return string
	 */
	public function callBack($content, $replace = '') {
		if ($this->contentInjectionCallBack !== '') {
			$_content = call_user_func_array($this->contentInjectionCallBack, array($this->packList));
			if ($this->contentInjectionPosition == 'before') {
				$content = $replace . $_content . $content;
			} elseif ($this->contentInjectionPosition == 'after') {
				$content .= $replace . $_content . $replace;
			}
		}
		return $content;
	}

	/**
	 * 检查打包方法的有效性
	 *
	 * @param string $packMethod 被检查的方法
	 * @return boolean
	 */
	private function isValidatePackMethod($packMethod) {
		return method_exists($this, $packMethod) && in_array($packMethod, 
			array(WindPack::STRIP_PHP, WindPack::STRIP_SELF, WindPack::STRIP_TOKEN));
	}
}