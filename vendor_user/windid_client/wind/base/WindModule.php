<?php
/**
 * 所有module的基础抽象类
 * 
 * 在module类中基本实现了对组件特性的支持,包括:配置解析,延迟加载,类代理以及提供获取基础对象的方法.
 * 如果需要用组件配置管理方式创建类对象,需要继承该类.支持config路径解析.
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindModule.php 3850 2012-12-04 07:30:02Z yishuo $
 * @package base
 */
class WindModule {
	/**
	 * 代理类对象
	 * 
	 * @var WindClassProxy
	 */
	public $_proxy = null;
	/**
	 * 配置数据
	 * 
	 * @var array
	 */
	protected $_config = array();
	/**
	 * 在延迟加载策略中使用,保存需要延迟加载的属性配置信息
	 * 
	 * @var array
	 */
	protected $_delayAttributes = array();

	/**
	 * 重载了魔术方法__set
	 * 当属性访问不到时该方法被调用,该方法会尝试去访问对应属性的setter设置器,如果不存在则什么也不做
	 * 
	 * @param string $propertyName
	 * @param mixed $value
	 * @return void
	 */
	public function __set($propertyName, $value) {
		$_setter = 'set' . ucfirst($propertyName);
		if (method_exists($this, $_setter)) $this->$_setter($value);
	}

	/**
	 * 重载了魔术方法__get
	 * 当属性访问不到时该方法被调用,该方法会尝试去访问对应属性的getter并返回对应的值,如果不存在则什么也不做
	 * 
	 * @param string $propertyName
	 * @return mixed
	 */
	public function __get($propertyName) {
		$_getter = 'get' . ucfirst($propertyName);
		if (method_exists($this, $_getter)) return $this->$_getter();
	}

	/**
	 * 重载了魔术方法__call
	 * 当类的方法访问不到时调用该方法,在这里的实现是配置类属性对象的延迟加载策略
	 * <code>
	 * //延迟访问某个属性,当使用这种方式调用时该方法被调用,并访问该类中的$_delayAttributes属性,并创建该属性对象并返回
	 * $this->_getMethodName();
	 * </code>
	 * 
	 * @param string $methodName
	 * @param array $args
	 * @return mixed
	 */
	public function __call($methodName, $args) {
		$_prefix = substr($methodName, 0, 4);
		$_propertyName = substr($methodName, 4);
		$_propertyName = WindUtility::lcfirst($_propertyName);
		if ($_prefix == '_get') {
			if (!$this->$_propertyName && isset($this->_delayAttributes[$_propertyName])) {
				$_property = $this->_delayAttributes[$_propertyName];
				$_value = null;
				if (isset($_property['value'])) {
					$_value = $_property['value'];
				} elseif (isset($_property['ref'])) {
					$_value = Wind::getComponent($_property['ref'], $args);
				} elseif (isset($_property['path'])) {
					$_className = Wind::import($_property['path']);
					$_value = WindFactory::createInstance($_className, $args);
				}
				$this->$_propertyName = $_value;
			}
			return $this->$_propertyName;
		} elseif ($_prefix == '_set') {
			$this->$_propertyName = $args[0];
		}
	}

	/**
	 * 返回该对象的数组类型
	 * 
	 * @return array
	 */
	public function toArray() {
		$reflection = new ReflectionClass(get_class($this));
		$properties = $reflection->getProperties();
		$_result = array();
		foreach ($properties as $property) {
			$_propertyName = $property->name;
			$_result[$_propertyName] = $this->$_propertyName;
		}
		return $_result;
	}

	/**
	 * 根据配置名取得相应的配置
	 * 当<i>configName</i>为空时则返回整个配置.当配置值不存在时返回默认值.默认值默认为空
	 * 
	 * @param string $configName 键名
	 * @param string $subConfigName 二级键名
	 * @param string $default 默认值
	 * @param array $config 外部配置
	 * @return mixed
	 */
	public function getConfig($configName = '', $subConfigName = '', $default = '', $config = array()) {
		if ($configName === '') return $this->_config;
		if (!isset($this->_config[$configName])) return $default;
		if ($subConfigName === '') return $this->_config[$configName];
		if (!isset($this->_config[$configName][$subConfigName])) return $default;
		return $this->_config[$configName][$subConfigName];
	}

	/**
	 * 设置类配置
	 * 设置类配置信息,如果配置已经存在,则将以存在配置和输入配置进行合并.
	 * 重复配置后者将覆盖前者.
	 * 支持配置路径解析,当输入值为配置路径时则会调用配置解析器进行解析并自动缓存当前配置值.(缓存是由wind_config中的isCache配置值决定是否开启)
	 * 
	 * @param string|array $config
	 * @return void
	 */
	public function setConfig($config) {
		if ($config) {
			if (is_string($config)) {
				$config = Wind::getComponent('configParser')->parse($config, get_class($this), false, 
					Wind::getComponent('windCache'));
			}
			if (!empty($this->_config)) {
				$this->_config = array_merge($this->_config, (array) $config);
			} else
				$this->_config = $config;
		}
	}

	/**
	 * 返回当前应用的WindHttpRequest对象
	 * 
	 * @return WindHttpRequest
	 */
	protected function getRequest() {
		return Wind::getComponent('request');
	}

	/**
	 * 返回当前应用的WindHttpResponse对象
	 * 
	 * @return WindHttpResponse
	 */
	protected function getResponse() {
		return Wind::getComponent('response');
	}

	/**
	 * 设置延迟加载类属性相关组件配置信息
	 * 
	 * @param array $delayAttributes
	 * @return void
	 */
	public function setDelayAttributes($delayAttributes) {
		$this->_delayAttributes = array_merge($this->_delayAttributes, $delayAttributes);
	}
}