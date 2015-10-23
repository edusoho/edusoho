<?php
/**
 * 学校
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindidSchoolService.php 24685 2013-02-05 04:28:51Z jieyin $
 * @package service.school.srv
 */
class WindidSchoolService {
	
	/**
	 * 查询学校
	 *
	 * @param WindidSchoolSo $searchSo
	 * @param int $limit
	 * @param int $start
	 * @return array
	 */
	public function searchSchool(WindidSchoolSo $searchSo, $limit = 10, $start = 0) {
		$list = $this->_getSchoolDs()->searchSchool($searchSo, $limit, $start);
		$result = array();
		foreach ($list as $_item) {
			$result[$_item['areaid']][$_item['schoolid']] = array('name' => $_item['name'], 'letter' => $_item['first_char']);
		}
		return $result;
	}

	/**
	 * 获得学校名称的第一个字母
	 *
	 * @param string $name
	 * @return string
	 */
	public function getFirstChar($name) {
		$asc = ord($name[0]);
		if ($asc < 160) { //非中文
			if ($asc >= 48 && $asc <= 57) {
				return $name[0]; //数字
			} elseif (($asc >= 65 && $asc <= 90) || ($asc >= 97 && $asc <= 122)) {
				return strtoupper($name[0]); // A--Z
			} else {
				return '~'; //其他
			}
		} else {//中文
			$str = iconv("UTF-8", "gb2312", $name);
			$asc = ord($str[0]) * 1000 + ord($str[1]);
			//获取拼音首字母A--Z
			if ($asc >= 176161 && $asc < 176197) {
				return 'A';
			} elseif ($asc >= 176197 && $asc < 178193) {
				return 'B';
			} elseif ($asc >= 178193 && $asc < 180238) {
				return 'C';
			} elseif ($asc >= 180238 && $asc < 182234) {
				return 'D';
			} elseif ($asc >= 182234 && $asc < 183162) {
				return 'E';
			} elseif ($asc >= 183162 && $asc < 184193) {
				return 'F';
			} elseif ($asc >= 184193 && $asc < 185254) {
				return 'G';
			} elseif ($asc >= 185254 && $asc < 187247) {
				return 'H';
			} elseif ($asc >= 187247 && $asc < 191166) {
				return 'J';
			} elseif ($asc >= 191166 && $asc < 192172) {
				return 'K';
			} elseif ($asc >= 192172 && $asc < 194232) {
				return 'L';
			} elseif ($asc >= 194232 && $asc < 196195) {
				return 'M';
			} elseif ($asc >= 196195 && $asc < 197182) {
				return 'N';
			} elseif ($asc >= 197182 && $asc < 197190) {
				return 'O';
			} elseif ($asc >= 197190 && $asc < 198218) {
				return 'P';
			} elseif ($asc >= 198218 && $asc < 200187) {
				return 'Q';
			} elseif ($asc >= 200187 && $asc < 200246) {
				return 'R';
			} elseif ($asc >= 200246 && $asc < 203250) {
				return 'S';
			} elseif ($asc >= 203250 && $asc < 205218) {
				return 'T';
			} elseif ($asc >= 205218 && $asc < 206244) {
				return 'W';
			} elseif ($asc >= 206244 && $asc < 209185) {
				return 'X';
			} elseif ($asc >= 209185 && $asc < 212209) {
				return 'Y';
			} elseif ($asc >= 212209) {
				return 'Z';
			} else {
				return '~';
			}
		}
	}

	/**
	 * 获得学校Ds
	 *
	 * @return WindidSchool
	 */
	private function _getSchoolDs() {
		return Wekit::load('WSRV:school.WindidSchool');
	}
}