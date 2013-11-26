<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 配置信息数据访问层
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidConfigDao.php 24445 2013-01-30 09:06:32Z jieyin $
 * @package config
 */
class WindidConfigDao extends WindidBaseDao {

	protected $_table = 'config';

	/**
	 * 根据空间名字获得该配置信息
	 *
	 * @param stirng $namespace 空间名字
	 * @return array
	 */
	public function getConfigs($namespace) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE namespace=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($namespace), 'name');
	}
	
	/**
	 * 根据空间名字获得该配置信息
	 *
	 * @param array $namespace 空间名字序列
	 * @return array
	 */
	public function fetchConfigs($namespace) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE namespace IN %s', $this->getTable(), $this->sqlImplode($namespace));
		$rst = $this->getConnection()->query($sql);
		return $rst->fetchAll();
	}

	/**
	 * 获取某个配置
	 *
	 * @param string $namespace
	 * @param string $name
	 * @return array
	 */
	public function getConfigByName($namespace, $name) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE namespace=? AND name=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($namespace, $name));
	}
	
	/**
	 * 批量设置配置项
	 *
	 * @param array $data 待设置的配置项
	 * @return boolean
	 */
	public function storeConfigs($data) {
		foreach ($data as $value) {
			$this->storeConfig($value['namespace'], $value['name'], $value['value']);
		}
		return true;
	}

	/**
	 * 存储配置项
	 * 
	 * @param string $namespace 配置项命名空间
	 * @param string $name 配置项名
	 * @param mixed $value 配置项的值
	 * @param string $descrip 配置项描述
	 * @return boolean
	 */
	public function storeConfig($namespace, $name, $value, $descrip = null) {
		$array = array();
		list($array['vtype'], $array['value']) = $this->_toString($value);
		isset($descrip) && $array['descrip'] = $descrip;
		if ($this->getConfigByName($namespace, $name)) {
			$sql = $this->_bindSql('UPDATE %s SET %s WHERE namespace=? AND name=?', $this->getTable(), $this->sqlSingle($array));
			$smt = $this->getConnection()->createStatement($sql);
			$result = $smt->update(array($namespace, $name));
		} else {
			$array['name'] = $name;
			$array['namespace'] = $namespace;
			$sql = $this->_bindSql('INSERT INTO %s SET %s', $this->getTable(), $this->sqlSingle($array));
			$result = $this->getConnection()->execute($sql);
		}
		PwSimpleHook::getInstance('PwConfigDao_update')->runDo($namespace);
		return $result;
	}

	/**
	 * 删除配置项
	 *
	 * @param string $namespace 配置项所属空间
	 * @return boolean
	 */
	public function deleteConfig($namespace) {
		$sql = $this->_bindTable('DELETE FROM %s WHERE namespace=?');
		$smt = $this->getConnection()->createStatement($sql);
		$result = $smt->update(array($namespace));
		PwSimpleHook::getInstance('PwConfigDao_update')->runDo($namespace);
		return $result;
	}

	/**
	 * 删除配置项
	 *
	 * @param string $namespace 配置项所属空间
	 * @param string $name 配置项名字
	 * @return boolean
	 */
	public function deleteConfigByName($namespace, $name) {
		$sql = $this->_bindTable('DELETE FROM %s WHERE namespace=? AND name=?');
		$smt = $this->getConnection()->createStatement($sql);
		$result = $smt->update(array($namespace, $name));
		PwSimpleHook::getInstance('PwConfigDao_update')->runDo($namespace);
		return $result;
	}

	/**
	 * 将数据转换为字符串
	 *
	 * @param mixed $value 待处理的数据
	 * @return array 返回处理后的数据，第一个代表该数据的类型，第二个代表该数据处理后的数据串
	 */
	private function _toString($value) {
		$vtype = 'string';
		if (is_array($value)) {
			$value = serialize($value);
			$vtype = 'array';
		} elseif (is_object($value)) {
			$value = serialize($value);
			$vtype = 'object';
		}
		return array($vtype, $value);
	}
}