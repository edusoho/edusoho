<?php
Wind::import('WIND:http.IWindHttpContainer');
/**
 * 会话机制，依赖Cache机制实现，应用可以根据自己的需求配置需要的存储方式实现会话存储
 * 【配置】支持组件配置格式:
 * <pre>
 * 'windSession' => array(
 * 'path' => 'WIND:http.session.WindSession',
 * 'scope' => 'singleton',
 * 'destroy' => 'commit',
 * 'constructor-args' => array(
 * '0' => array(
 * 'ref' => 'windCache',
 * ),
 * ),
 * ),
 * </pre>
 * 【使用】调用时使用：
 * <pre>
 * $session = Wind::getComponent('WindSession');
 * 
 * $session->set('name', 'test');    //等同：$_SESSION['name'] = 'test';
 * echo $session->get('name');       //等同：echo $_SESSION['name'];
 * 
 * $session->delete('name');         //等同： unset($_SESSION['name');
 * echo $session->getCurrentName();     //等同： echo session_name();
 * echo $session->getCurrentId();       //等同： echo session_id();
 * $session->destroy();              //等同： session_unset();session_destroy();
 * </pre>
 * 【使用原生】：
 * 如果用户不需要配置自己其他存储方式的session，则不许要修改任何调用，只要在WindSession的配置中将constructor-args配置项去掉即可。如下：
 * <pre>
 * 'windSession' => array(
 * 'path' => 'WIND:http.session.WindSession',
 * 'scope' => 'singleton',
 * 'destroy' => 'commit',
 * ),
 * </pre>
 * 
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindSession.php 3791 2012-10-30 04:01:29Z liusanbian $
 * @package http
 * @subpackage session
 */
class WindSession extends WindModule implements IWindHttpContainer {

	/**
	 * 构造函数
	 * 
	 * @param AbstractWindCache $dataStoreHandler 数据缓存对象,默认为null
	 * @param object $sessionHandler  session操作设置类,默认为null
	 * @return void
	 */
	public function __construct($dataStoreHandler = null, $sessionHandler = null) {
		$this->setDataStoreHandler($dataStoreHandler, $sessionHandler);
	}

	/**
	 * 开启session
	 * 
	 * @return void
	 */
	public function start() {
		'' === $this->getCurrentId() && session_start();
	}

	/**
	 * 设置数据
	 * 
	 * @param string $key 保存在session中的键名
	 * @param mixed $value 保存在session中的值
	 * @return void
	 */
	public function set($key, $value) {
		$key && $_SESSION[$key] = $value;
	}

	/**
	 * 获得数据
	 * 
	 * @param string $key 保存在session中的键名
	 * @return mixed 返回保存在session中该键名对应的键值
	 */
	public function get($key) {
		return $this->isRegistered($key) ? $_SESSION[$key] : '';
	}

	/**
	 * 删除数据
	 * 
	 * @param string $key
	 */
	public function delete($key) {
		$_SESSION[$key] = null;
		unset($_SESSION[$key]);
		return true;
	}

	/**
	 * 清除会话信息
	 * 
	 * @return boolean
	 */
	public function destroy() {
		return session_destroy();
	}

	/**
	 * 检测变量是否已经被注册
	 * 
	 * @param string $key 需要进行判断的建名
	 * @return boolean 如果已经被注册则返回true,否则返回false
	 */
	public function isRegistered($key) {
		return isset($_SESSION[$key]);
	}

	/**
	 * 获得当前session的名字
	 * 
	 * @return string
	 */
	public function getCurrentName() {
		return session_name();
	}

	/**
	 * 设置当前session的名字
	 * 
	 * @param string $name session的名字
	 * @return boolean 设置成功将返回true
	 */
	public function setCurrentName($name) {
		return session_name($name);
	}

	/**
	 * 获得sessionId
	 * 
	 * @return string
	 */
	public function getCurrentId() {
		return session_id();
	}

	/**
	 * 设置当前session的Id
	 * 
	 * @param string $id 需要设置的id名
	 * @return boolean 设置成功返回true
	 */
	public function setCurrentId($id) {
		return session_id($id);
	}

	/**
	 * 写入session之后关闭session
	 * 
	 * 同session_write_close
	 * 
	 * @return void
	 */
	public function commit() {
		return session_commit();
	}

	/**
	 * 设置链接对象
	 * 
	 * @param AbstractWindCache $handler  session数据的缓存介质
	 * @param object $sessionHandler session操作接口的定义类
	 */
	public function setDataStoreHandler($dataStoreHandler = null, $sessionHandler = null) {
		if ($dataStoreHandler) {
			if ($sessionHandler === null) {
				Wind::import('WIND:http.session.handler.WindSessionHandler');
				$sessionHandler = new WindSessionHandler();
			}
			$sessionHandler->registerHandler($dataStoreHandler);
		}
		$this->start();
	}
}