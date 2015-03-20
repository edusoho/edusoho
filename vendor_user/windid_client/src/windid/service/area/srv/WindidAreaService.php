<?php

/**
 * 地区库的service
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindidAreaService.php 28948 2013-05-31 05:02:59Z jieyin $
 * @package service.area.srv
 */
class WindidAreaService {
	
	/**
	 * 获取地区的省市信息
	 *
	 * @param int $areaid
	 * @return array  返回array('省', '市', '县')
	 */
	public function getAreaInfo($areaid) {
		$info = $this->_getWindidAreaDs()->getArea($areaid);
		if (!$info) return array();
		return explode('|', $info['joinname']);
	}
	
	/**
	 * 根据地区ID获得该地区的从第一级开始的路径
	 * 返回一个数组，该数组从第一个元素为最顶层的路径
	 * 
	 * @param int $areaid
	 * @return array
	 */
	public function getAreaRout($areaid) {
		if (!$areaid) return array();
		$info = $this->_getWindidAreaDs()->getArea($areaid);
		if (!$info) return array();
		$rout = array();
		array_unshift($rout, $info);
		$parentid = $info['parentid'];
		while($parentid > 0) {
			$_info = $this->_getWindidAreaDs()->getArea($parentid);
			array_unshift($rout, $_info);
			$parentid = $_info['parentid'];
		}
		return $rout;
	}

	/**
	 * 根据地区ID列表获得地区
	 *
	 * @param array $areaids
	 * @return array
	 */
	public function fetchAreaInfo($areaids) {
		if (!$list = $this->_getWindidAreaDs()->fetchByAreaid($areaids)) {
			return array();
		}
		$array = array();
		foreach ($list as $key => $value) {
			$array[$key] = str_replace('|', ' ', $value['joinname']);
		}
		return $array;
	}
	
	/**
	 * 批量获取地区的路径
	 * 返回地区，每一个地区的路径元素都保存 省级/市级/地区级
	 *
	 * @param array $areaids
	 * @return array
	 */
	public function fetchAreaRout($areaids) {
		$list = $this->_getWindidAreaDs()->fetchByAreaid($areaids);
		if (!$list) return array();
		$routs = $parents = array();
		foreach ($list as $key => $_item) {
			if (!$_item['parentid']) {
				$routs[$key] = array($key, '', '');
			} else {
				$routs[$key] = array('', $_item['parentid'], $key);
				$parents[$_item['parentid']] = $key;
			}
		}
		if (!$parents) return $routs;
		$list = $this->_getWindidAreaDs()->fetchByAreaid(array_keys($parents));
		foreach ($list as $key => $_item) {
			$tmp = $routs[$parents[$key]];
			if (!$_item['parentid']) {
				$tmp[0] = $key;
				$tmp[1] = $parents[$key];
				$tmp[2] = '';
			} else {
				$tmp[0] = $_item['parentid'];
				$tmp[1] = $key;
				$tmp[2] = $parents[$key];
			}
			$routs[$parents[$key]] = $tmp;
		}
		return $routs;
	}
	
	/**
	 * 获得地区数据
	 * 支持3三级： 省-市-区
	 *
	 * @param int $selected
	 * @return array
	 */
	public function getAreaTree() {
		$areas = $this->_getWindidAreaDs()->fetchAll();
		//$areas = $this->getCacheArea(); //本地，从缓存获取
		if (!is_array($areas)) return array();
		$root = array();
		foreach ($areas as $areaid => $item) {
			if ($item['parentid'] == 0) {
				$root[$areaid] = array('name' => $item['name']);
				unset($areas[$areaid]);
			}
		}
		foreach ($root as $areaid => $item) {
			$childs = $this->_buildTree($areas, $areaid);
			$childs && $root[$areaid]['items'] = $childs;
		}
		return $root;
	}
	
	/**
	 * 构建地区树
	 * 
	 * @param array $areas
	 * @param int $parentid
	 * @return array
	 */
	private function _buildTree(&$areas, $parentid) {
		$childs = $temp = array();
		foreach ($areas as $areaid => $item) {
			if ($item['parentid'] == $parentid) {
				if (!isset($childs[$areaid])) {
					$childs[$areaid] = array('name' => $item['name']);
				}
				$temp[] = $areaid;
			} elseif ($areas[$item['parentid']]['parentid'] == $parentid) {
				if (!isset($childs[$areas[$item['parentid']]['areaid']])) {
					$childs[$areas[$item['parentid']]['areaid']] = array('name' => $areas[$item['parentid']]['name']);
					$temp[] = $areas[$item['parentid']]['areaid'];
				}
				$childs[$areas[$item['parentid']]['areaid']]['items'][$areaid] = $item['name']; 
				$temp[] = $areaid;
			}
		}
		foreach (array_unique($temp) as $_k) {
			unset($areas[$_k]);
		}
		return $childs;
	}
	
	/**
	 * 根据地区ID获得该地区下级
	 *
	 * @param int $areaid
	 * @return array
	 */
	public function getAreaByParentid($areaid, $selected = 0) {
		$list = $this->_getWindidAreaDs()->getAreaByParentid($areaid);
		$return = array();
		foreach ($list as $item) {
			$return[] = array($item['areaid'], $item['name'], $item['areaid'] == $selected ? 1 : 0);
		}
		return $return;
	}
	
	/**
	 * 更新缓存
	 * 
	 * @return boolean
	 */
	public function updateCache() {
		$data = $this->_getWindidAreaDs()->fetchAll();
		$file = Wind::getRealPath('DATA:area.area.php', true);
		WindFolder::mk(dirname($file));
		WindFile::savePhpData($file, $data, true);
		return $data;
	}
	
	protected function getCacheArea() {
		$file = Wind::getRealPath('DATA:area.area.php', true);
		if (WindFile::isFile($file)) return include $file;
		return $this->updateCache();
	}
	
	/**
	 * 获得windid的地区DS
	 *
	 * @return WindidArea
	 */
	private function _getWindidAreaDs() {
		return Wekit::load('WSRV:area.WindidArea');
	}
}