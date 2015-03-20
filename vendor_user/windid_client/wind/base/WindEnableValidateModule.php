<?php
/**
 * 表单验证基类
 * 
 * 注入：验证器/异常处理器
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindEnableValidateModule.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package base
 */
class WindEnableValidateModule {
	/**
	 * 验证类
	 *
	 * @var string
	 */
	protected $_validatorClass = 'WIND:utility.WindValidator';
	/**
	 * 错误处理action
	 *
	 * @var string
	 */
	protected $errorAction = '';
	/**
	 * 验证类实例
	 *
	 * @var WindValidator
	 */
	private $_validator = null;
	/**
	 * 验证中产生的错误信息
	 *
	 * @var array
	 */
	protected $_errors = array();
	/**
	 * 验证中产生错误信息时使用的默认错误信息
	 *
	 * @var string
	 */
	private $_defaultMessage = 'the field validate fail.';

	/**
	 * 返回验证中产生的错误信息
	 * 
	 * @return array $_errors
	 */
	public function getErrors() {
		return $this->_errors;
	}

	/**
	 * 返回验证出错时使用的错误errorAction
	 * 
	 * errorAction的格式可以用/分割m,c,a三个部分:完整的方式为m/c/a
	 *
	 * @return string
	 */
	public function getErrorAction() {
		return $this->errorAction;
	}

	/**
	 * 返回验证规则组成的数组
	 * 
	 * 每一个验证规则都需要如下格式:
	 * <code>
	 * 	array(
	 * 		'field' => 'name',	//验证的字段名
	 * 		'default' => 'test',	//如果字段为空
	 * 		'args' => array('args1', 'args2'),	//validator验证方法接受的其他参数(必须在被验证参数的后面)
	 * 		'validator' => 'isEmpty',	//执行的验证方法(该方法必须在$_validatorClass指向的验证类中存在并可公开访问，并且该方法返回boolean类型)
	 * 		'message' => 'name is empty',	//验证失败时返回的错误信息
	 * 	)
	 * </code>
	 * 验证规则可以采用{@link WindUtility::buildValidateRule()}方法进行构造.
	 * 
	 * @return array
	 */
	protected function validateRules() {
		return array();
	}
	
	/**
	 * 验证方法
	 * 
	 * @return void
	 */
	public function validate() {
		$rules = $this->validateRules();
		$methods = get_class_methods($this);
		foreach ((array) $rules as $rule) {
			$getMethod = 'get' . ucfirst($rule['field']);
			$_input = in_array($getMethod, $methods) ? call_user_func(array($this, $getMethod)) : '';
			if (true === $this->check($_input, $rule)) continue;
			$setMethod = 'set' . ucfirst($rule['field']);
			in_array($setMethod, $methods) && call_user_func_array(array($this, $setMethod), array($rule['default']));
		}
	}
	
	/**
	 * 执行rule验证
	 *
	 * @param string $input 待验证的数据
	 * @param array $rule 数据验证的规则
	 * @return boolean 如果验证成功返回true,验证失败返回false
	 */
	private function check($input, $rule) {
		$arg = (array) $rule['args'];
		array_unshift($arg, $input);
		if (call_user_func_array(array($this->getValidator(), $rule['validator']), $arg) !== false) return true;
		if ($rule['default'] === null) {
			$this->_errors[$rule['field']] = $rule['message'];
			return true;
		}
		return false;
	}

	/**
	 * 返回验证器
	 * 
	 * @return WindValidator
	 * @throws WindException 验证器创建失败抛出异常
	 */
	protected function getValidator() {
		if ($this->_validator === null) {
			$_className = Wind::import($this->_validatorClass);
			$this->_validator = WindFactory::createInstance($_className);
			if ($this->_validator === null) throw new WindException('[base.WindEnableValidateModule.getValidator] validator', WindException::ERROR_RETURN_TYPE_ERROR);
		}
		return $this->_validator;
	}
}