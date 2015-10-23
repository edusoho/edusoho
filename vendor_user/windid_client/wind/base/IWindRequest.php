<?php
/**
 * request接口定义
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: IWindRequest.php 3829 2012-11-19 11:13:22Z yishuo $
 * @package http
 * @subpackage request
 */
interface IWindRequest {

	/**
	 * 返回包含由客户端提供的、跟在真实脚本名称之后并且在查询语句（query string）之前的路径信息
	 *
	 * @return string
	 * @throws WindException
	 */
	public function getPathInfo();

	/**
	 * 获得请求类型
	 *
	 * 如果是web请求将返回web
	 * @return string
	 */
	public function getRequestType();

	/**
	 * 获得主机信息，包含协议信息，主机名，访问端口信息
	 *
	 * @return string
	 * @throws WindException 获取主机信息失败的时候抛出异常
	 */
	public function getHostInfo();

	/**
	 * 返回当前运行脚本所在的服务器的主机名。
	 *
	 * 如果脚本运行于虚拟主机中
	 * 该名称是由那个虚拟主机所设置的值决定
	 * @return string
	 */
	public function getServerName();

	/**
	 * 返回服务端口号
	 *
	 * https链接的默认端口号为443
	 * http链接的默认端口号为80
	 * @return int
	 */
	public function getServerPort();

	/**
	 * 返回客户端程序期望服务器返回哪个国家的语言文档
	 *
	 * Accept-Language: en-us,zh-cn
	 * @return string
	 */
	public function getAcceptLanguage();

	/**
	 * 设置属性数据
	 *
	 * @param string|array|object $data 需要设置的数据
	 * @param string $key 设置的数据保存用的key,默认为空,当数组和object类型的时候将会执行array_merge操作
	 * @return void
	 */
	public function setAttribute($data, $key = '');

	/**
	 * 根据名称获得服务器和执行环境信息
	 *
	 * 主要获取的依次顺序为：_attribute、$_GET、$_POST、$_COOKIE、$_REQUEST、$_ENV、$_SERVER
	 * @param string $name 获取数据的key值
	 * @param string $defaultValue 设置缺省值,当获取值失败的时候返回缺省值,默认该值为空字串
	 * @return string|object|array 返回获得值
	 */
	public function getAttribute($key, $defaultValue = '');
}




