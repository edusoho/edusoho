<?php
/**
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com/license.php
 * @version $Id$
 * @package wind
 */
class PwHookDao extends PwBaseDao {
	protected $_table = 'hook';
	protected $_pk = 'name';
	protected $_dataStruct = array('name', 'app_name', 'app_id', 'created_time', 'modified_time', 'document');

	/**
	 * 添加钩子定义
	 *
	 * @param array $fields        	
	 * @return boolean
	 */
	public function add($fields) {
		if (!$fields = $this->_filterStruct($fields)) return false;
		$sql = $this->_bindTable('INSERT INTO %s SET ') . $this->sqlSingle($fields);
		return $this->getConnection()->createStatement($sql)->execute();
	}
	
	/**
	 * 编辑钩子
	 *
	 * @param string $name
	 * @param array $fields
	 * @return boolean
	 */
	public function update($name, $fields) {
		return $this->_update($name, $fields);
	}

	/**
	 * 批量注册钩子定义，返回影响行数
	 *
	 * @param array $fields
	 * @return int
	 */
	public function batchAdd($fields) {
		foreach ($fields as $key => $value) {
			$_tmp = array();
			$_tmp['name'] = $value['name'];
			$_tmp['app_name'] = $value['app_name'];
			$_tmp['app_id'] = $value['app_id'];
			$_tmp['created_time'] = intval($value['created_time']);
			$_tmp['modified_time'] = intval($value['modified_time']);
			$_tmp['document'] = $value['document'];
			$fields[$key] = $_tmp;
		}
		$sql = $this->_bindSql(
			'INSERT INTO %s (`name`,`app_name`,`app_id`,`created_time`,`modified_time`,`document`) VALUES %s', 
			$this->getTable(), $this->sqlMulti($fields));
		return $this->getConnection()->createStatement($sql)->execute();
	}

	/**
	 * 根据App_id删除钩子信息
	 *
	 * @param string $app_id
	 * @return boolean
	 */
	public function delByAppId($app_id) {
		$sql = $this->_bindTable('DELETE FROM %s WHERE app_id=?');
		return $this->getConnection()->createStatement($sql)->execute(array($app_id));
	}
	
	/**
	 * 根据应用名称删除
	 *
	 * @param string $appName
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function delByAppName($appName) {
		$sql = $this->_bindTable('DELETE FROM %s WHERE app_name = ?');
		return $this->getConnection()->createStatement($sql)->execute(array($appName));
	}

	/**
	 * 根据钩子名称删除钩子定义
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function delByName($name) {
		$sql = $this->_bindTable('DELETE FROM %s WHERE name=?');
		return $this->getConnection()->createStatement($sql)->execute(array($name));
	}

	/**
	 * 根据名称批量删除hook
	 *
	 * @param array $names
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function batchDelByName($names) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE name IN %s', $this->getTable(), $this->sqlImplode($names));
		return $this->getConnection()->createStatement($sql)->execute();
	}

	/**
	 * 根据hook name 更新，返回影响行数
	 *
	 * @param string $name
	 * @return int
	 */
	public function updateByName($name) {
		if (!$fields = $this->_filterStruct($fields)) return false;
		$sql = $this->_bindTable('UPDATE %s set ') . $this->sqlSingle($fields) . ' WHERE name=?';
		return $this->getConnection()->createStatement($sql)->execute(array($name));
	}

	/**
	 * 根据name查找hook注册信息，返回hook数据
	 *
	 * @param string $appId        	
	 * @return array
	 */
	public function find($name) {
		if (!$name) return false;
		$sql = $this->_bindTable('SELECT * FROM %s ') . ' WHERE name=?';
		return $this->getConnection()->createStatement($sql)->getOne(array($name));
	}

	/**
	 * 根据应用ID查找Hook信息
	 *
	 * @param int $appIds
	 * @return Ambigous <multitype:, multitype:multitype: Ambigous <multitype:, multitype:unknown , mixed> >
	 */
	public function findByAppId($appId) {
		$sql = $this->_bindTable('SELECT * FROM %s ') . ' WHERE app_id=?';
		return $this->getConnection()->createStatement($sql)->queryAll(array($appId));
	}

	/**
	 * 根据Hook名称获取Hook信息
	 *
	 * @param string $name
	 * @return array
	 */
	public function findByName($name) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE name=?');
		return $this->getConnection()->createStatement($sql)->getOne(array($name));
	}

	/**
	 * 根据hook name查找hook注册信息，返回hook数据
	 *
	 * @param string $names
	 * @return array
	 */
	public function batchFindByName($names) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE name IN %s', $this->getTable(), $this->sqlImplode($names));
		return $this->getConnection()->createStatement($sql)->queryAll(array(), 'name');
	}

	/**
	 * 分页查找钩子信息
	 *
	 * @param int $num 默认为10
	 * @param int $start
	 * @param int $index
	 * @param string $order
	 * @return boolean|Ambigous <multitype:, multitype:multitype: Ambigous <multitype:, multitype:unknown , mixed> >
	 */
	public function findByPage($num = 10, $start = 0, $index = 'name', $order = 'name') {
		if (!in_array($order, $this->_dataStruct)) return false;
		$sql = $this->_bindSql('SELECT * FROM %s ORDER BY `' . $order . '` %s', $this->getTable(), 
			$this->sqlLimit($num, $start));
		return $this->getConnection()->createStatement($sql)->queryAll(array(), $index);
	}

	/**
	 * 根据hook名称搜索
	 *
	 * @param string $name
	 * @param int $num
	 * @param int $start
	 * @return array
	 */
	public function searchHook($fields, $num = 10, $start = 0) {
		list($where, $values) = $this->_buildCondition($fields);
		empty($where) && $where = '1';
		$sql = $this->_bindSql('SELECT * FROM %s WHERE %s ORDER BY name %s', $this->getTable(), $where, 
			$this->sqlLimit($num, $start));
		return $this->getConnection()->createStatement($sql)->queryAll($values, 'name');
	}

	/**
	 * 获取数据总条数
	 *
	 * @return int
	 */
	public function count() {
		$sql = $this->_bindTable('SELECT COUNT(*) FROM %s');
		return $this->getConnection()->createStatement($sql)->getValue();
	}

	private function _buildCondition($fields) {
		$conditions = array();
		$values = array();
		foreach ($fields as $k => $v) {
			switch ($k) {
				case 'name':
					$conditions[] = 'name like ?';
					$values[] = "$v%";
					break;
				case 'app_name':
					$conditions[] = 'app_name like ?';
					$values[] = "%$v%";
					break;
			}
		}
		return array(implode(' AND ', $conditions), $values);
	}
}

?>