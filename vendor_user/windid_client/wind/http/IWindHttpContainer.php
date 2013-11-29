<?php
/**
 * http容器类接口定义
 * 
 * http容器类接口定义,http container 需要继承该类,统一http容器接口定义.接口:<code>
 * 1. set 向容器中设置值
 * 2. get 获取内容值
 * 3. delete 删除内容值
 * </code>
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-19
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: IWindHttpContainer.php 3113 2011-11-11 07:28:09Z yishuo $
 * @package http
 */
interface IWindHttpContainer {

	/**
	 * 向容器中设置值
	 * 
	 * @param string $key 
	 * @param mixed $value
	 * @return boolean
	 */
	public function set($key, $value);

	/**
	 * 获取容器中的内容值
	 * 
	 * @param string $key
	 */
	public function get($key);

	/**
	 * 删除容器中的值
	 * 
	 * @param string $key 
	 */
	public function delete($key);

	/**
	 * 检测变量是否已经被注册
	 * 
	 * @param string $key 需要进行判断的建名
	 * @return boolean 如果已经被注册则返回true,否则返回false
	 */
	public function isRegistered($key);

}

?>