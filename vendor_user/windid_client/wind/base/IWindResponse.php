<?php
/**
 * response的接口定义
 *
 * @author Qiong Wu <papa0924@gmail.com> 2010-11-7
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: IWindResponse.php 3533 2012-05-08 08:24:20Z yishuo $
 * @package http
 * @subpackage response
 */
interface IWindResponse {

	/**
	 * 获得保存输出数据
	 *
	 * @param string $var
	 * @return mixed
	 */
	public function getData();

	/**
	 * 设置保存输出数据
	 *
	 * @param mixed $data 待保存的输出数据
	 * @param string $key 输出数据的key名称,默认为空
	 */
	public function setData($data, $key = '');

	/**
	 * 获得输出的编码方式
	 *
	 * @return string
	 */
	public function getCharset();

	/**
	 * 设置输出的编码方式
	 *
	 * @param string $_charset 编码方式
	 * @return void
	 */
	public function setCharset($_charset);

	/**
	 * 设置响应内容
	 *
	 * @param string $content 响应内容信息
	 * @param string $name 相应内容片段名字,默认为null
	 * @return void
	 */
	public function setBody($content, $name = 'default');

	/**
	 * 发送一个错误的响应信息
	 *
	 * @param int $status 错误码,默认为404
	 * @param string $message 错误信息,默认为空
	 * @return void
	 */
	public function sendError($status = self::W_NOT_FOUND, $message = '');

	/**
	 * 发送响应内容
	 *
	 * @return void
	 */
	public function sendBody();

	/**
	 * 发送响应头部信息
	 *
	 * @return void
	 */
	public function sendHeaders();

	/**
	 * 发送响应信息
	 *
	 * 依次发送响应头和响应内容
	 * @return void
	 */
	public function sendResponse();

}