<?php
/**
 * 应用基础接口
 * 
 * 应用基础接口,该接口包含4个接口<i>run,getRequest,getResponse,getWindFactory</i>,自定义应用类型需要实现该接口.
 * 基础实现有<i>WindWebApplication</i>
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: IWindApplication.php 3829 2012-11-19 11:13:22Z yishuo $
 * @package base
 */
interface IWindApplication { 

	/**
	 * 调用该方法启动应用
	 * 
	 * @return void
	 * @throws Exception
	 */
	public function run();

	/**
	 * 返回WindHttpRequest
	 * 
	 * @return WindHttpRequest $request
	 */
	public function getRequest();

	/**
	 * 返回WindHttpResponse
	 * 
	 * @return WindHttpResponse $response
	 */
	public function getResponse();

	/**
	 * 返回WindFactory
	 * 
	 * @return WindFactory $windFactory
	 */
	public function getFactory();
}
?>