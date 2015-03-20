<?php
/**
 * 工厂类
 * 工厂类职责:加载组件配置并创建并管理组件对象作用域以及生命周期.通过组件工厂的方式创建组件对象,简化了组件的创建过程,统一了组件的管理接口并且具有很好的可扩展性.
 * 组件配置方式<code>
 * <component name='' path='' factory-method='' init-method=''
 * scope="application/singleton/prototype/" proxy='' destroy=''>
 * <property property-name='' ref/value/path=''>
 * <constructor-arg ref/value=''>
 * <config resource=''>
 * </component></code>支持定义类对象的别名,路径,工厂方法,初始化方法,作用域,是否启动代理,析构方法,属性值,构造参数,以及配置解析等.
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindFactory.php 3914 2013-01-23 03:07:17Z yishuo $
 * @package base
 */
class WindFactory {
	protected $proxyType = 'WIND:filter.proxy.WindClassProxy';
	protected $classDefinitions = array();
	protected $instances = array();
	protected $prototype = array();
	protected $destories = array();
	protected $singleton = array();
	private static $_instance = null;

	/**
	 * 初始化工厂类
	 * 
	 * @param array $classDefinitions
	 *        组件定义 默认值为空数组
	 */
	private function __construct() {}
	
	/*
	 * (non-PHPdoc) @see IWindFactory::getInstance()
	 */
	public function getInstance($alias, $args = array()) {
		$instance = null;
		$definition = isset($this->classDefinitions[$alias]) ? $this->classDefinitions[$alias] : array();
		if (isset($this->prototype[$alias])) {
			$instance = clone $this->prototype[$alias];
			if (isset($definition['destroy'])) $this->destories[] = array($instance, $definition['destroy']);
		} elseif (isset($this->instances[$alias])) {
			$instance = $this->instances[$alias];
		} elseif (isset($this->singleton[$alias])) {
			$instance = $this->singleton[$alias];
		} else {
			if (!$definition) return null;
			$_unscope = empty($args);
			if (isset($definition['constructor-args']) && $_unscope) $this->buildArgs($definition['constructor-args'], 
				$args);
			if (!isset($definition['className'])) $definition['className'] = Wind::import(@$definition['path']);
			$instance = $this->createInstance($definition['className'], $args);
			if (isset($definition['config'])) $this->resolveConfig($definition['config'], $alias, $instance);
			if (isset($definition['properties'])) $this->buildProperties($definition['properties'], $instance);
			if (isset($definition['initMethod'])) $this->executeInitMethod($definition['initMethod'], $instance);
			!isset($definition['scope']) && $definition['scope'] = 'application';
			$_unscope && $this->setScope($alias, $definition['scope'], $instance);
			if (isset($definition['destroy'])) $this->destories[$alias] = array($instance, $definition['destroy']);
		}
		if (isset($definition['proxy'])) {
			$listeners = isset($definition['listeners']) ? $definition['listeners'] : array();
			$instance = $this->setProxyForClass($definition['proxy'], $listeners, $instance);
		}
		return $instance;
	}
	
	/*
	 * (non-PHPdoc) @see IWindFactory::createInstance()
	 */
	static public function createInstance($className, $args = array()) {
		try {
			if (empty($args)) {
				return new $className();
			} else {
				$reflection = new ReflectionClass($className);
				return call_user_func_array(array($reflection, 'newInstance'), (array) $args);
			}
		} catch (Exception $e) {
			throw new WindException('[base.WindFactory] create instance \'' . $className . '\' fail.' . $e->getMessage(), 
				WindException::ERROR_CLASS_NOT_EXIST);
		}
	}

	/**
	 * 注册组件对象,如果已经存在则覆盖原有值
	 * 
	 * @param object $instance        
	 * @param string $alias        
	 * @param string $scope
	 *        对象作用域 默认为'singleton'
	 * @return boolean
	 */
	public function registInstance($instance, $alias, $scope = 'singleton') {
		return $this->setScope($alias, $scope, $instance);
	}

	/**
	 * 动态添加组件定义
	 * 
	 * @param string $alias        
	 * @param array $classDefinition        
	 * @return void
	 * @throws WindException
	 */
	public function addClassDefinitions($alias, $classDefinition) {
		if (is_string($alias) && !empty($alias)) {
			if (!isset($this->classDefinitions[$alias])) $this->classDefinitions[$alias] = $classDefinition;
		} else
			throw new WindException('[base.WindFactory.addClassDefinitions] class alias is empty.', 
				WindException::ERROR_PARAMETER_TYPE_ERROR);
	}

	/**
	 * 加载类定义
	 * 调用该方法加载组件定义,如果merge为true,则覆盖原有配置信息.
	 * 
	 * @param array $classDefinitions        
	 * @param boolean $merge
	 *        是否进行merge操作,默认为true
	 * @return void
	 */
	public function loadClassDefinitions($classDefinitions, $merge = true) {
		foreach ((array) $classDefinitions as $alias => $definition) {
			if (!is_array($definition)) continue;
			if (isset($this->instances[$alias]) || isset($this->prototype[$alias])) continue;
			if (!isset($this->classDefinitions[$alias]) || $merge === false)
				$this->classDefinitions[$alias] = $definition;
			else
				$this->classDefinitions[$alias] = WindUtility::mergeArray($this->classDefinitions[$alias], $definition);
		}
	}

	/**
	 * 设置类定义
	 * 通过该方法设置会覆盖原有类定义，请注意该方法于{@see loadClassDefinitions}的区别
	 * 
	 * @param array $classDefinitions        
	 */
	public function setClassDefinitions($classDefinitions) {
		$this->classDefinitions = $classDefinitions;
	}

	/**
	 * 组件定义检查
	 * 检查类定义是否已经存在,或者是否已经被创建.避免重复注册组件定义
	 * 
	 * @param string $alias        
	 * @return boolean
	 */
	public function checkAlias($alias) {
		if (isset($this->prototype[$alias]))
			return true;
		elseif (isset($this->instances[$alias]))
			return true;
		return false;
	}

	/**
	 * 执行组件定义的注销方法
	 * 调用全部组件的注销方法,按照组件的定义顺序依次调用,相关组件的定义方法<code><component
	 * destroy=''>...</component></code>
	 * 该方法在应用结束时统一被调用.
	 * 
	 * @return void
	 * @throws WindException
	 */
	public function executeDestroyMethod() {
		try {
			foreach ($this->destories as $call)
				call_user_func_array($call, array());
			$this->instances = array();
			$this->destories = array();
		} catch (Exception $e) {
			throw new WindException($e->getMessage());
		}
	}

	/**
	 * 解析组件对象构造方法参数信息,并返回参数列表
	 * 该方法解析组件对象构造方法的参数信息,相关的组件配置<code><constructor-args>
	 * <constructor-arg name='0' value='DATA:log' />
	 * <constructor-arg name='1' value='2' />
	 * </constructor-args></code>相关定义同properties相同.'name'为参数位置,生成的参数列表按照'name'一次排序.
	 * 
	 * @param array $constructors        
	 * @param array $args        
	 * @return void
	 */
	protected function buildArgs($constructors, &$args) {
		foreach ((array) $constructors as $key => $_var) {
			$key = intval($key);
			if (isset($_var['value'])) {
				$args[$key] = $_var['value'];
			} elseif (isset($_var['ref']))
				$args[$key] = $this->getInstance($_var['ref']);
			elseif (isset($_var['path'])) {
				$_className = Wind::import($_var['path']);
				$args[$key] = $this->createInstance($_className);
			}
		}
		ksort($args);
	}

	/**
	 * 组件对象的作用域解析
	 * 组件对象的作用域解析,目前支持的属性作用于为'prototype','application','singleton',默认为'application'.
	 * 相关组件定义方式<code><component scope=''>...</component></code>
	 * 
	 * @param string $alias        
	 * @param string $scope        
	 * @param WindModule $instance        
	 * @return boolean
	 */
	protected function setScope($alias, $scope, $instance) {
		switch ($scope) {
			case 'prototype':
				$this->prototype[$alias] = clone $instance;
				break;
			case 'application':
				$this->instances[$alias] = $instance;
				break;
			case 'singleton':
				$this->singleton[$alias] = $instance;
				break;
			default:
				break;
		}
		return true;
	}

	/**
	 * 解析组件配置
	 * 解析组件配置并将配置信息设置进组件,默认调用windCache组件对进行配置缓存处理,可以通过配置'isCache'开启或关闭系统缓存.
	 * 组件配置定义<code><component ...><config
	 * resource=''>...</config></component></code>,
	 * 可以通过配置'resource'引入外部配置(支持命名空间方式定义路径地址),也可以直接将配置定义到config标签下.
	 * 
	 * @param array|string $config        
	 * @param string $alias        
	 * @param WindModule $instance        
	 * @return void
	 */
	protected function resolveConfig($config, $alias, $instance) {
		if (!empty($config['resource'])) {
			$_configPath = Wind::getRealPath($config['resource'], true, true);
			$config = $this->getInstance('configParser')->parse($_configPath, $alias, 'components_config', 
				(!empty($this->instances['windCache']) ? $this->instances['windCache'] : null));
		}
		if ($config && method_exists($instance, 'setConfig')) $instance->setConfig($config);
	}

	/**
	 * 执行类的初始化方法
	 * 类的初始化方法的组件定义<code><component init-method=''>...</component></code>
	 * 
	 * @param string $initMethod        
	 * @param object $instance        
	 * @return mixed
	 * @throws WindException
	 */
	protected function executeInitMethod($initMethod, $instance) {
		try {
			return $instance->$initMethod();
			// return call_user_func_array(array($instance, $initMethod),
			// array());
		} catch (Exception $e) {
			throw new WindException(
				'[base.WindFactory.executeInitMethod] (' . $initMethod . ', ' . $e->getMessage() . ')', 
				WindException::ERROR_CLASS_METHOD_NOT_EXIST);
		}
	}

	/**
	 * 设置类代理对象,并返回代理类对象
	 * 代理类同当前类拥有同样的职责和功能,可以通过访问代理类对象访问当前类.
	 * 类的代理信息对应的组件配置为<code><component
	 * proxy=''>...</component></code>当'proxy'设置为false时返回该类的代理类型,
	 * 类默认的代理类型为WindClassProxy,不可以通过配置进行修改.
	 * 
	 * @param string $proxy        
	 * @param WindModule $instance        
	 * @return WindClassProxy
	 */
	protected function setProxyForClass($proxy, $listeners, $instance) {
		if (!$proxy) return $instance;
		if (true === $proxy) $proxy = $this->proxyType;
		/* @var $proxy WindClassProxy */
		$proxy = self::createInstance(Wind::import($proxy));
		$proxy->registerTargetObject($instance);
		foreach ($listeners as $key => $value) {
			$listener = WindFactory::createInstance(Wind::import($value));
			$proxy->registerEventListener($listener, $key);
		}
		// $instance->_proxy = $proxy;
		return $proxy;
	}

	/**
	 * 构建类的属性信息
	 * 类的属性信息对应的组件配置为<code><properties delay=''><property property-name=''
	 * ref/value/path='' />
	 * </properties></code>他支持的标签为'delay','property-name','ref','value','path'.
	 * 当'delay'设置为true时该属性延迟加载.'property-name'设置了属性名称.'ref','value','path'是定义属性值的三种方式,'ref'指向另外的组件定义名称,
	 * 'value'直接输入属性的值,'path'定义一个属性对象的路径,系统会自动创建该属性的对象.
	 * 
	 * @param string $properties
	 *        属性定义
	 * @param WindModule $instance
	 *        类对象
	 * @return void
	 */
	protected function buildProperties($properties, $instance) {
		isset($properties['delay']) || $properties['delay'] = true;
		if ($properties['delay'] === 'false' || $properties['delay'] === false) {
			foreach ($properties as $key => $subDefinition) {
				$_value = '';
				if (isset($subDefinition['value']))
					$_value = $subDefinition['value'];
				elseif (isset($subDefinition['ref']))
					$_value = $this->getInstance($subDefinition['ref']);
				elseif (isset($subDefinition['path'])) {
					$_className = Wind::import($subDefinition['path']);
					$_value = $this->createInstance($_className);
				}
				$_setter = 'set' . ucfirst(trim($key, '_'));
				if (method_exists($instance, $_setter)) call_user_func_array(array($instance, $_setter), array($_value));
			}
		} else
			$instance->setDelayAttributes($properties);
	}

	/**
	 * 返回组建工厂单例对象
	 *
	 * @return WindFactory
	 */
	public static function _getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}