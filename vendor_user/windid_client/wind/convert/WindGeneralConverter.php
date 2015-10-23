<?php
Wind::import('WIND:convert.IWindConverter');
/**
 * 通用编码转化类
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-19
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindGeneralConverter.php 3016 2011-10-20 02:23:08Z yishuo $
 * @package convert
 */
class WindGeneralConverter extends WindModule implements IWindConverter {
	protected $tableName = './encode/encode.table';
	protected $tableHandle = 0;
	protected $encodeLang = '';
	protected $iconvEnabled = false;
	protected $tableIndex = array();
	protected $tableEncode = array();
	
	protected $IndexPoint = array(
		'GBKtoUTF8' => 0, 
		'GBKtoUNICODE' => 0, 
		'BIG5toUTF8' => 1024, 
		'BIG5toUNICODE' => 1024, 
		'UTF8toGBK' => 512, 
		'UTF8toBIG5' => 1536);

	/**
	 * @param string $SourceLang
	 * @param string $TargetLang
	 * @param boolean $ForceTable
	 */
	public function WindGeneralConverter($sourceLang = '', $targetLang = '', $forceTable = false) {
		if ($sourceLang && $targetLang) $this->initConvert($sourceLang, $targetLang, $forceTable);
	}

	/**
	 * 编码转化
	 * 
	 * 对输入的字符串进行从原编码到目标编码的转化,请确定原编码与目标编码
	 * @param string $srcText
	 * @param string $SourceLang
	 * @param string $TargetLang
	 * @param boolean $ForceTable
	 * @return Ambigous <string, unknown>|string|unknown
	 */
	public function convert($srcText, $sourceLang = '', $targetLang = '', $forceTable = false) {
		if ($sourceLang && $targetLang) $this->initConvert($sourceLang, $targetLang, $forceTable);
		
		if ($this->encodeLang) {
			switch ($this->encodeLang) {
				case 'GBKtoUTF8':
					return $this->iconvEnabled ? iconv('GBK', 'UTF-8', $srcText) : $this->chstoUTF8($srcText);
					break;
				case 'BIG5toUTF8':
					return $this->iconvEnabled ? iconv('BIG5', 'UTF-8', $srcText) : $this->chstoUTF8($srcText);
					break;
				case 'UTF8toGBK':
					return $this->iconvEnabled ? iconv('UTF-8', 'GBK', $srcText) : $this->utf8toCHS($srcText);
					break;
				case 'UTF8toBIG5':
					return $this->utf8toCHS($srcText);
					break;
				case 'GBKtoUNICODE':
					return $this->chstoUNICODE($srcText, 'GBK');
					break;
				case 'BIG5toUNICODE':
					return $this->chstoUNICODE($srcText, 'BIG5');
					break;
				case 'GBKtoBIG5':
				case 'BIG5toGBK':
					return $this->chsConvert($srcText, $this->encodeLang);
					break;
			}
		}
		return $srcText;
	}

	/**
	 * 初始化类
	 *
	 * @param string $SourceLang
	 * @param string $TargetLang
	 * @param boolean $ForceTable
	 */
	protected function initConvert($SourceLang, $targetLang, $forceTable) {
		if (($SourceLang = $this->_getCharset($SourceLang)) && ($targetLang = $this->_getCharset($targetLang)) && $SourceLang != $targetLang) {
			$this->encodeLang = $SourceLang . 'to' . $targetLang;
			$this->iconvEnabled = (function_exists('iconv') && $targetLang != 'BIG5' && !$forceTable) ? true : false;
			$this->iconvEnabled || is_resource($this->tableHandle) || $this->tableHandle = fopen($this->tableName, "r");
		}
	}

	/**
	 * unicode to utf8
	 *
	 * @param string $c
	 * @return string
	 */
	protected function unicodeToUTF8($c) {
		if ($c < 0x80) {
			$c = chr($c);
		} elseif ($c < 0x800) {
			$c = chr(0xC0 | $c >> 6) . chr(0x80 | $c & 0x3F);
		} elseif ($c < 0x10000) {
			$c = chr(0xE0 | $c >> 12) . chr(0x80 | $c >> 6 & 0x3F) . chr(0x80 | $c & 0x3F);
		} elseif ($c < 0x200000) {
			$c = chr(0xF0 | $c >> 18) . chr(0x80 | $c >> 12 & 0x3F) . chr(0x80 | $c >> 6 & 0x3F) . chr(0x80 | $c & 0x3F);
		} elseif ($c < 0x4000000) {
			$c = chr(0xF8 | $c >> 24) . chr(0xF0 | $c >> 18) . chr(0x80 | $c >> 12 & 0x3F) . chr(0x80 | $c >> 6 & 0x3F) . chr(
				0x80 | $c & 0x3F);
		} else {
			$c = chr(0xF8 | $c >> 30) . chr(0xF8 | $c >> 24) . chr(0xF0 | $c >> 18) . chr(0x80 | $c >> 12 & 0x3F) . chr(
				0x80 | $c >> 6 & 0x3F) . chr(0x80 | $c & 0x3F);
		}
		return $c;
	}

	/**
	 * BIG5,GBK to Unicode
	 *
	 * @param string $c
	 * @return string
	 */
	protected function chsUTF8toU($c) {
		switch (strlen($c)) {
			case 1:
				return ord($c);
			case 2:
				return ((ord($c[0]) & 0x3F) << 6) + (ord($c[1]) & 0x3F);
			case 3:
				return ((ord($c[0]) & 0x1F) << 12) + ((ord($c[1]) & 0x3F) << 6) + (ord($c[2]) & 0x3F);
			case 4:
				return ((ord($c[0]) & 0x0F) << 18) + ((ord($c[1]) & 0x3F) << 12) + ((ord($c[2]) & 0x3F) << 6) + (ord(
					$c[3]) & 0x3F);
		}
	}

	/**
	 * BIG5,GBK to UTF8
	 *
	 * @param string $srcText
	 * @return Ambigous <string, unknown>
	 */
	protected function chstoUTF8($srcText) {
		$this->_getTableIndex();
		$tarText = '';
		for ($i = 0; $i < strlen($srcText); $i += 2) {
			$h = ord($srcText[$i]);
			if ($h > 127 && isset($this->tableIndex[$this->encodeLang][$h])) {
				$l = ord($srcText[$i + 1]);
				if (!isset($this->tableEncode[$this->encodeLang][$h][$l])) {
					fseek($this->tableHandle, $l * 2 + $this->tableIndex[$this->encodeLang][$h]);
					$this->tableEncode[$this->encodeLang][$h][$l] = $this->unicodeToUTF8(
						hexdec(bin2hex(fread($this->tableHandle, 2))));
				}
				$tarText .= $this->tableEncode[$this->encodeLang][$h][$l];
			} elseif ($h < 128) {
				$tarText .= $srcText[$i];
				$i--;
			}
		}
		return $tarText;
	}

	/**
	 * UTF8 to GBK,BIG5
	 * 
	 * @param string $srcText
	 * @return string
	 */
	protected function utf8toCHS($srcText) {
		$this->_getTableIndex();
		$tarText = '';
		$i = 0;
		while ($i < strlen($srcText)) {
			$c = ord($srcText[$i++]);
			switch ($c >> 4) {
				case 0:
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
				case 7:
					$tarText .= chr($c);
					break;
				case 12:
				case 13:
					$c = (($c & 0x1F) << 6) | (ord($srcText[$i++]) & 0x3F);
					$h = $c >> 8;
					if (isset($this->tableIndex[$this->encodeLang][$h])) {
						$l = $c & 0xFF;
						if (!isset($this->tableEncode[$this->encodeLang][$h][$l])) {
							fseek($this->tableHandle, $l * 2 + $this->tableIndex[$this->encodeLang][$h]);
							$this->tableEncode[$this->encodeLang][$h][$l] = fread($this->tableHandle, 2);
						}
						$tarText .= $this->tableEncode[$this->encodeLang][$h][$l];
					}
					break;
				case 14:
					$c = (($c & 0x0F) << 12) | ((ord($srcText[$i++]) & 0x3F) << 6) | ((ord($srcText[$i++]) & 0x3F));
					$h = $c >> 8;
					if (isset($this->tableIndex[$this->encodeLang][$h])) {
						$l = $c & 0xFF;
						if (!isset($this->tableEncode[$h][$l])) {
							fseek($this->tableHandle, $l * 2 + $this->tableIndex[$this->encodeLang][$h]);
							$this->tableEncode[$h][$l] = fread($this->tableHandle, 2);
						}
						$tarText .= $this->tableEncode[$h][$l];
					}
					break;
			}
		}
		return $tarText;
	}

	/**
	 * GBK,BIG5 to UNICODE
	 *
	 * @param string $srcText
	 * @param string $SourceLang
	 * @return Ambigous <string, unknown>
	 */
	protected function chstoUNICODE($srcText, $SourceLang = '') {
		$tarText = '';
		if ($this->iconvEnabled && isset($SourceLang)) {
			for ($i = 0; $i < strlen($srcText); $i += 2) {
				if (ord($srcText[$i]) > 127) {
					$tarText .= "&#x" . dechex(
						$this->Utf8_Unicode(iconv($SourceLang, "UTF-8", $srcText[$i] . $srcText[$i + 1]))) . ";";
				} else {
					$tarText .= $srcText[$i--];
				}
			}
		} else {
			$this->_getTableIndex();
			for ($i = 0; $i < strlen($srcText); $i += 2) {
				$h = ord($srcText[$i]);
				if ($h > 127 && isset($this->tableIndex[$this->encodeLang][$h])) {
					$l = ord($srcText[$i + 1]);
					if (!isset($this->tableEncode[$this->encodeLang][$h][$l])) {
						fseek($this->tableHandle, $l * 2 + $this->tableIndex[$this->encodeLang][$h]);
						$this->tableEncode[$this->encodeLang][$h][$l] = '&#x' . bin2hex(fread($this->tableHandle, 2)) . ';';
					}
					$tarText .= $this->tableEncode[$this->encodeLang][$h][$l];
				} elseif ($h < 128) {
					$tarText .= $srcText[$i--];
				}
			}
		}
		return $tarText;
	}

	/**
	 * BIG5toGBK
	 * 
	 * @param string $srcText
	 * @param string $SourceLang
	 * @return mixed
	 */
	protected function chsConvert($srcText, $SourceLang = 'GBK') {
		if (strtoupper(substr($SourceLang, 0, 3)) == 'GBK') {
			$handle = fopen('./encode/gb-big5.table', "r");
		} else {
			$handle = fopen('./encode/big5-gb.table', "r");
		}
		$encode = array();
		for ($i = 0; $i < strlen($srcText) - 1; $i++) {
			$h = ord($srcText[$i]);
			if ($h >= 160) {
				$l = ord($srcText[$i + 1]);
				if (!isset($encode[$h][$l])) {
					fseek($handle, ($h - 160) * 510 + ($l - 1) * 2);
					$encode[$h][$l] = fread($handle, 2);
				}
				$srcText[$i++] = $encode[$h][$l][0];
				$srcText[$i] = $encode[$h][$l][1];
			}
		}
		fclose($handle);
		return $srcText;
	}

	/**
	 * 解析编码表
	 * 
	 * @return void
	 */
	private function _getTableIndex() {
		if (!isset($this->tableIndex[$this->encodeLang])) {
			fseek($this->tableHandle, $this->IndexPoint[$this->encodeLang]);
			$tmpData = fread($this->tableHandle, 512);
			$pFirstEncode = hexdec(bin2hex(substr($tmpData, 4, 4)));
			for ($i = 8; $i < 512; $i += 4) {
				$item = unpack('nkey/nvalue', substr($tmpData, $i, 4));
				if (isset($this->tableIndex[$this->encodeLang][$item['key']])) break;
				$this->tableIndex[$this->encodeLang][$item['key']] = $pFirstEncode + $item['value'];
			}
		}
	}

	/**
	 * 获得编码类型
	 *
	 * @param string $lang
	 * @return string
	 */
	private function _getCharset($lang) {
		switch (strtoupper(substr($lang, 0, 2))) {
			case 'GB':
				$lang = 'GBK';
				break;
			case 'UT':
				$lang = 'UTF8';
				break;
			case 'UN':
				$lang = 'UNICODE';
				break;
			case 'BI':
				$lang = 'BIG5';
				break;
			default:
				$lang = '';
		}
		return $lang;
	}
}
?>