<?php
/**
 * 注册session处理的方法
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindSessionHandler.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package http
 * @subpackage session.handler
 */
class WindSessionHandler extends AbstractWindSessionHandler {

	/* (non-PHPdoc)
	 * @see AbstractWindSessionHandler::open()
	 */
	public function open($savePath, $sessionName) {
		if ('0' == ($expire = $this->dataStore->getExpire())) {
			$lifeTime = get_cfg_var("session.gc_maxlifetime");
			$this->dataStore->setExpire((int) $lifeTime);
		} else
			session_cache_expire($expire);
		return true;
	}

	/* (non-PHPdoc)
	 * @see AbstractWindSessionHandler::close()
	 */
	public function close() {
		return true;
	}

	/* (non-PHPdoc)
	 * @see AbstractWindSessionHandler::write()
	 */
	public function write($sessID, $sessData) {
		return $this->dataStore->set($sessID, $sessData);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindSessionHandler::read()
	 */
	public function read($sessID) {
		return $this->dataStore->get($sessID);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindSessionHandler::gc()
	 */
	public function gc($maxlifetime) {
		return true;
	}

	/* (non-PHPdoc)
	 * @see AbstractWindSessionHandler::destroy()
	 */
	public function destroy($sessID) {
		return $this->dataStore->delete($sessID);
	}
}

/**
 * 注册sessionHandler的接口定义类
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindSessionHandler.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package http
 * @subpackage session.handler
 */
abstract class AbstractWindSessionHandler {
	/**
	 * 保存session数据的实例
	 * 
	 * @var AbstractWindCache
	 */
	protected $dataStore = null;

	/**
	 * 在开始会话时调用初始化会话信息
	 * 
	 * 用以从从保存的介质中获取session数据
	 * 
	 * @param string $savePath 保存的地址
	 * @param string $sessionName  会话的名字
	 * @return  boolean
	 */
	abstract public function open($savePath, $sessionName);

	/**
	 * 关闭会话存储存储机制
	 * 
	 * 在页面执行完的时候执行
	 * 
	 * @return  boolean
	 */
	abstract public function close();

	/**
	 * 将sessionID对应的数据写到存储
	 * 
	 * 在sessionClose之前执行写入session数据的
	 * 
	 * @param string $sessID 会话ID
	 * @param mixed $sessData 该会话产生的数据 
	 * @return void
	 */
	abstract public function write($sessID, $sessData);

	/**
	 * 从存储中装载session数据
	 * 
	 * 在执行session_start的时候执行在open之后
	 * 
	 * @param string $sessid 会话ID
	 * @return void
	 */
	abstract public function read($sessID);

	/**
	 * 对存储系统中的数据进行垃圾收集
	 * 
	 * 在执行session过期策略的时候执行，注意，session的过期并不是时时的，需要根据php.ini中的配置项：
	 * session.gc_probability = 1
	 * session.gc_divisor = 1000  
	 * 执行的概率是gc_probability/gc_divisor .
	 * session.gc_maxlifetime = 1440  设置的session的过期时间
	 * 
	 * @param int $maxlifetime 过期时间单位秒
	 * @return void
	 */
	abstract public function gc($maxlifetime);

	/**
	 * 销毁与指定的会话ID相关联的数据
	 * 
	 * 在执行session_destroy的时候执行。
	 * 
	 * @param string $sessID 会话ID
	 * @return void 
	 */
	abstract public function destroy($sessID);

	/**
	 * 设置session的存储方法及注册session中各个handler
	 * 
	 * @param AbstractWindCache $dataStore 存储方式
	 * @return void
	 */
	public function registerHandler($dataStore) {
		if (!$dataStore instanceof AbstractWindCache) {
			throw new WindException('[http.session.WindSessionHandler.registerHandler] register session save handler fail.', WindException::ERROR_PARAMETER_TYPE_ERROR);
		}
		$this->dataStore = $dataStore;
		session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array(
			$this, 
			'write'), array($this, 'destroy'), array($this, 'gc'));
	}
}