<?php
Wind::import('WIND:fitler.WindHandlerInterceptor');
/**
 * action拦截器父类
 * 继承实现拦截链preHandle（前置）和postHandle（后置）职责.将实现的拦截链添加到应用配置中,使之生效:
 * 例如实现MyFilter,则需要在应用配置中添加如下配置:
 * <code>
 * 'filters' => array(
 * 'class' => 'WIND:filter.WindFilterChain',	//设置使用的拦截链实现
 * 'filter1' => array(
 * 'class' =>
 * 'MYAPP:filter.MyFilter',	//设置设置实现的MyFilter类路径,MYAPP必须是一个有效的经过注册的命名空间
 * 'pattern' => '*',	//此处设置该拦截规则应用的范围,*意味着所有的action都将会应用该拦截规则
 * )
 * )
 * </code>
 * 关于pattern的设置说明如下：
 * <ul>
 * <li>*：则所有的请求都将会应用该拦截器</li>
 * <li>moduleA*: 则所有配置的moduleA模块下的请求都将会应用该拦截器</li>
 * <li>moduleA_index*: 则moduleA模块下的indexController下的所有Action请求都将会应用该拦截器</li>
 * <li>moduleA_index_add*: 则module模块下的indexController下的addAction将会应用该拦截器</li>
 * </ul>
 * 用户可以在filter中添加自己的特殊配置:比如:
 * <code>
 * 'filters' => array(
 * 'class' => 'WIND:filter.WindFilterChain',
 * 'filter1' => array(
 * 'class' => 'MYAPP:filter.TestFilter',
 * 'pattern' => '*',
 * 'isOpen' => '1',	//添加的配置
 * )
 * )
 * </code>
 * 则在自己的TestFilter中设置一个属性名为isOpen同时设置该属性为protected权限,那么在使用的时候该配置的值将会赋值给该属性.
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindActionFilter.php 3829 2012-11-19 11:13:22Z yishuo $
 * @package filter
 */
abstract class WindActionFilter extends WindHandlerInterceptor {
	/**
	 * action跳转类
	 * 
	 * @var WindForward
	 */
	protected $forward = null;
	/**
	 * 错误处理类
	 * 
	 * @var WindErrorMessage
	 */
	protected $errorMessage = null;
	/**
	 * 路由对象
	 * 
	 * @var AbstractWindRouter
	 */
	protected $router = null;

	/**
	 * 构造函数
	 * 初始化类属性
	 * 
	 * @param WindForward $forward
	 *        设置当前的forward对象
	 * @param WindErrorMessage $errorMessage
	 *        设置错误处理的errorMessage
	 * @param AbstractWindRouter $router
	 *        路由对象
	 * @param array $args
	 *        接受数组传递,数组以关联数组的方式给出,如果存在属性和关联数组中的key相同则将该key对应值设置给该属性.
	 */
	public function __construct($forward, $errorMessage, $router, $args = array()) {
		$this->forward = $forward;
		$this->errorMessage = $errorMessage;
		$this->router = $router;
		foreach ($args as $key => $value)
			property_exists($this, $key) && $this->$key = $value;
	}

	/**
	 * 设置模板数据
	 * 此方法设置的参数,作用域仅仅只是在当前模板中可用,调用的方法为{$varName}
	 * 
	 * @param string|array|object $data
	 *        需要设置输出的参数
	 * @param string $key
	 *        参数的名字,默认为空，如果key为空，并且$data是数组或是对象的时候，则$data中的元素将会作为单独的参数保存到输出数据中.
	 */
	protected function setOutput($data, $key = '') {
		$this->forward->setVars($data, $key);
	}

	/**
	 * 从指定源中根据输入的参数名获得输入数据
	 * 
	 * @param string $name
	 *        需要获取的值的key
	 * @param string $type
	 *        获取数据源,可以是(GET POST COOKIE)中的一个,每种都将从各自的源中去获取对应的数值:
	 *        <ul>
	 *        <li>GET: 将从$_GET中去获取数据</li>
	 *        <li>POST: 将从$_POST中去获取数据</li>
	 *        <li>COOKIE: 将从$_COOKIE中去获取数据</li>
	 *        <li>其他值:
	 *        将依次从request对象的attribute,$_GET,$_POST,$_COOKIE,$_REQUEST,$_ENV,$_SERVER中去尝试获取该值.</li>
	 *        </ul>
	 *        该参数默认为空
	 * @return array string <ul>
	 *         <li>第一个元素: 获得的用户输入的值</li>
	 *         <li>第二个元素：执行$callback之后返回的值</li>
	 *         </ul>
	 */
	protected function getInput($name, $type = '') {
		$value = '';
		switch (strtolower($type)) {
			case 'get':
				$value = $this->getRequest()->getGet($name);
				break;
			case 'post':
				$value = $this->getRequest()->getPost($name);
				break;
			case 'cookie':
				$value = $this->getRequest()->getCookie($name);
				break;
			default:
				$value = $this->getRequest()->getRequest($name);
		}
		return $value;
	}
}
?>