<?php
/**
 * form表单拦截器
 * 
 * form表单拦截器允许用户配置实现form表单验证:
 * <note><b>注意：</b>
 * <ul>
 * <li>form表单必须继承WindEnableValidateModule;</li>
 * <li>form表单中的所有属性都必须设置setVar和getVar存取方法对;</li>
 * <li>form表单实现的验证配置validateRules，也将会在设置完属性之后执行验证;</li>
 * <li>该表单在验证完成之后,会将该表单实例以[表单类名]为名字保存在Request中,在获取的时候只要通过request->getInput('表单类名')获取即可;</li>
 * </ul>
 * </note>
 * 
 * 该formFilter接受一个配置form，该配置项指向需要过滤的表单类文件.
 * 例如：
 * <code>
 * 'filters' => array(
 * 'class' => 'WIND:filter.WindFilterChain',	
 * 'filter1' => array(
 * 'class' => 'WIND:web.filter.WindFormFilter',	//将filter指定为该formFilter
 * 'pattern' => '*',	
 * 'form' => 'MY:form.MyForm',
 * )
 * )
 * </code>
 * 在action中获取的时候使用:
 * <code>
 * $this->getInput('myForm');//获得配置的过滤表单
 * </code>  
 *
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindFormFilter.php 3533 2012-05-08 08:24:20Z yishuo $
 * @package web
 * @subpackage filter
 */
class WindFormFilter extends WindActionFilter {
	
	/**
	 * 验证的表单类
	 *
	 * @var string
	 */
	protected $form = '';

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::preHandle()
	 */
	public function preHandle() {
		if (!$this->form) return null;
		$className = Wind::import($this->form);
		$form = WindFactory::createInstance($className);
		$methods = get_class_methods($form);
		foreach ($methods as $method) {
			if ((0 !== strpos($method, 'set')) || ('' == ($_tmp = substr($method, 3)))) continue;
			if (null === ($input = $this->getRequest()->getRequest(WindUtility::lcfirst($_tmp), null))) continue;
			call_user_func_array(array($form, $method), array($input));
		}
		$form->validate();
		$this->getRequest()->setAttribute($form, WindUtility::lcfirst($className));
		$this->sendError($form->getErrorAction(), $form->getErrors());
	}

	/**
	 * 发送错误信息
	 *
	 * @param string $errorAction 需要处理错误的action配置模式为module/controller/action
	 * @param array $errors 验证中产生的错误信息
	 * @return void
	 */
	private function sendError($errorAction, $errors) {
		if (empty($errors)) return;
		$this->errorMessage->setErrorAction($errorAction);
		foreach ($errors as $key => $value)
			$this->errorMessage->addError($value, $key);
		$this->errorMessage->sendError();
	}

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::postHandle()
	 */
	public function postHandle() {}
}