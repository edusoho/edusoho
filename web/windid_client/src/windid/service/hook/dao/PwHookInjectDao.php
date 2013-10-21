<?php
/**
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com/license.php
 * @version $Id$
 * @package wind
 */
class PwHookInjectDao extends PwBaseDao {
	protected $_table = 'hook_inject';
	protected $_pk = 'id';
	protected $_dataStruct = array(
		'id', 
		'app_id',
		'app_name',
		'hook_name', 
		'alias', 
		'class', 
		'method', 
		'loadway', 
		'expression', 
		'created_time', 
		'modified_time', 
		'description');

	/**
	 * 添加钩子定义
	 *
	 * @param array $fields
	 * @return boolean
	 */
	public function add($fields) {
		if (!$fields = $this->_filterStruct($fields)) return false;
		$sql = $this->_bindTable('INSERT INTO %s SET ') . $this->sqlSingle($fields);
		$statement = $this->getConnection()->createStatement($sql);
		$statement->execute();
		return $this->getConnection()->lastInsertId();
	}

	/**
	 * 批量添加钩子扩展信息, 影响行数
	 *
	 * @param array $fields
	 * @return int
	 */
	public function batchAdd($fields) {
		foreach ($fields as $key => $value) {
			$_tmp = array();
			$_tmp['app_id'] = $value['app_id'];
			$_tmp['app_name'] = $value['app_name'];
			$_tmp['hook_name'] = $value['hook_name'];
			$_tmp['alias'] = $value['alias'];
			$_tmp['class'] = $value['class'];
			$_tmp['method'] = $value['method'];
			$_tmp['loadway'] = $value['loadway'];
			$_tmp['expression'] = $value['expression'];
			$_tmp['created_time'] = intval($value['created_time']);
			$_tmp['modified_time'] = intval($value['modified_time']);
			$_tmp['description'] = $value['description'];
			$fields[$key] = $_tmp;
		}
		$sql = $this->_bindSql(
			'INSERT INTO %s (`app_id`, `app_name`, `hook_name`, `alias`, `class`, `method`, `loadway`, `expression`, `created_time`, `modified_time`, `description`) VALUES %s', 
			$this->getTable(), $this->sqlMulti($fields));
		return $this->getConnection()->createStatement($sql)->execute();
	}

	/**
	 * 刪除hook，返回影响行数
	 *
	 * @param string $id
	 * @return int
	 */
	public function del($id) {
		$sql = $this->_bindTable('DELETE FROM %s WHERE id=?');
		return $this->getConnection()->createStatement($sql)->execute(array($id));
	}

	/**
	 * 根据钩子名称删除钩子定义
	 *
	 * @param string $alias
	 * @return boolean
	 */
	public function delByAlias($alias) {
		$sql = $this->_bindTable('DELETE FROM %s WHERE alias=?');
		return $this->getConnection()->createStatement($sql)->execute(array($alias));
	}

	/**
	 * 根据Inject id批量删除injector信息
	 *
	 * @param array $ids
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function batchDelById($ids) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE id IN %s', $this->getTable(), $this->sqlImplode($ids));
		return $this->getConnection()->createStatement($sql)->execute();
	}

	/**
	 * 根据别名，批量删除injector
	 *
	 * @param array $alias
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function batchDelByAlias($alias) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE alias IN %s', $this->getTable(), $this->sqlImplode($alias));
		return $this->getConnection()->createStatement($sql)->execute();
	}

	/**
	 * 根据HookName删除injector
	 *
	 * @param string $hookName
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function delByHookName($hookName) {
		$sql = $this->_bindTable('DELETE FROM %s WHERE hook_name=?');
		return $this->getConnection()->createStatement($sql)->execute(array($hookName));
	}

	/**
	 * 根据HookName删除injector
	 *
	 * @param array $hookNames
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function batchDelByHookName($hookNames) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE hook_name IN %s', $this->getTable(), $this->sqlImplode($hookNames));
		return $this->getConnection()->createStatement($sql)->execute();
	}

	/**
	 * 根据钩子名称和扩展别名删除一个扩展
	 *
	 * @param string $alias
	 * @param string $hookname
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function delByHookNameAndAlias($alias, $hookname) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE hook_name=? AND alias=?', $this->getTable());
		return $this->getConnection()->createStatement($sql)->execute(array($hookname, $alias));
	}

	/**
	 * 根据hook id 更新，返回影响行数
	 *
	 * @param string $id
	 * @param array $fields
	 * @return int
	 */
	public function update($id, $fields) {
		if (!$fields = $this->_filterStruct($fields)) return false;
		$sql = $this->_bindTable('UPDATE %s set ') . $this->sqlSingle($fields) . ' WHERE id=?';
		return $this->getConnection()->createStatement($sql)->execute(array($id));
	}

	/**
	 * 根据ID查找hook注册信息，返回hook数据
	 *
	 * @param string $appId
	 * @return array
	 */
	public function find($id) {
		if (!$id) return false;
		$sql = $this->_bindTable('SELECT * FROM %s ') . ' WHERE id=?';
		return $this->getConnection()->createStatement($sql)->getOne(array($id));
	}
	
	/**
	 * 根据id数据批量获取hook数据
	 *
	 * @param array $ids
	 * @return array
	 */
	public function fetch($ids) {
		return $this->_fetch($ids);
	}

	/**
	 * 根据HookName获取注入服务列表
	 *
	 * @param int $hookId
	 * @return array
	 */
	public function findByHookName($hookName) {
		$sql = $this->_bindTable('SELECT * FROM %s ') . ' WHERE hook_name=? ORDER BY `id`';
		return $this->getConnection()->createStatement($sql)->queryAll(array($hookName), 'alias');
	}
	
	/**
	 * 根据HookName批量获取注入服务列表
	 *
	 * @param int $hookId
	 * @return array
	 */
	public function fetchByHookName($hookNames) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE hook_name IN %s', $this->getTable(), $this->sqlImplode($hookNames));
		return $this->getConnection()->query($sql)->fetchAll('id');
	}

	/**
	 * 根据别名获取应用注如服务列表
	 *
	 * @param string $alias
	 * @return array
	 */
	public function findByAlias($alias) {
		$sql = $this->_bindTable('SELECT * FROM %s ') . ' WHERE alias=?';
		return $this->getConnection()->createStatement($sql)->queryAll(array($alias));
	}

	/**
	 * 根据别名批量查找注册服务
	 *
	 * @param array $alias
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function batchFindByAlias($alias) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE alias IN %s', $this->getTable(), $this->sqlImplode($alias));
		return $this->getConnection()->createStatement($sql)->queryAll(array(), $this->_pk);
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
	public function findByPage($num = 10, $start = 0, $index = 'id', $order = 'alias') {
		if (!in_array($order, $this->_dataStruct)) return false;
		$sql = $this->_bindSql('SELECT * FROM %s ORDER BY `' . $order . '` %s', $this->getTable(), 
			$this->sqlLimit($num, $start));
		return $this->getConnection()->createStatement($sql)->queryAll(array('style'), $index);
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
	
	/**
	 * 根据应用名称删除
	 *
	 * @param string $appName
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function deleteByAppName($appName) {
		$sql = $this->_bindTable('DELETE FROM %s WHERE `app_name` = ?');
		return $this->getConnection()->createStatement($sql)->execute(array($appName));
	}
	
	/**
	 * 根据应用id删除
	 *
	 * @param string $appName
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function deleteByAppId($appid) {
		$sql = $this->_bindTable('DELETE FROM %s WHERE `app_id` = ?');
		return $this->getConnection()->createStatement($sql)->execute(array($appid));
	}
	
	/**
	 * 根据appid获取应用注如服务列表
	 *
	 * @param string $appid
	 * @return array
	 */
	public function findByAppid($appid) {
		$sql = $this->_bindTable('SELECT * FROM %s ') . ' WHERE app_id=?';
		return $this->getConnection()->createStatement($sql)->queryAll(array($appid), $this->_pk);
	}
	
}

?>