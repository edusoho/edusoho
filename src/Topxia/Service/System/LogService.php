<?php

namespace Topxia\Service\System;

interface LogService
{
	/**
	 * 记录一般日志
	 * @param  string $module  模块
	 * @param  string $action  操作
	 * @param  string $message 记录的详情
	 * @return interger       最后的自增id号
	 */
	public function info($module, $action, $message);

	/**
	 * 记录警告日志
	 * @param  string $module  模块
	 * @param  string $action  操作
	 * @param  string $message 记录的详情
	 * @return interger       最后的自增id号
	 */
	public function warning($module, $action, $message);
	

	/**
	 * 记录错误日志
	 * @param  string $module  模块
	 * @param  string $action  操作
	 * @param  string $message 记录的详情
	 * @return interger       最后的自增id号
	 */
	public function error($module, $action, $message);


	/**
	 * 日志搜索
	 * @param  array   $conditions 搜索条件，如array(
	 *                             			"level"=>"info", 
	 *                             			"message"=>"描述",
	 *                             			"startDateTime"=> "21321342124",
	 *                             			"endDateTime"=> "23221324234323"
	 *                             			), 
	 *                             支持的键名有:
	 *                             level, id, userId, module, action,message, 
	 *                             ip, level, startDateTime, endDateTime
	 *                             
	 * @param  array   $sorts      按什么排序, 如array("createdTime"=>"DESC", "ip"=>"ASC"), 
	 *                             支持的键名请参考log数据
	 * @param  integer $start      开始行数
	 * @param  integer $limit      返回最多行数
	 * @return array        	   多维数组    
	 * 
	 */
	public function searchLogs($conditions, $sorts, $start, $limit);

	/**
	 * 根据指定搜索条件返回该条数。
	 * @param  array   $conditions 搜索条件，如array(
	 *                             			"level"=>"info", 
	 *                             			"message"=>"描述",
	 *                             			"startDateTime"=> "21321342124",
	 *                             			"endDateTime"=> "23221324234323"
	 *                             			), 
	 *                             			
	 *                             支持的键名有:
	 *                             level, id, userId, module, action,message, 
	 *                             ip, level, startDateTime, endDateTime
	 *                             
	 * @return interger           
	 */
	public function searchLogCount($conditions);
}