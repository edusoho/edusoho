<?php

namespace Topxia\Service\System;

interface LogService
{
	/**
	 * 记录一般日志
	 * @param  string $module  模块
	 * @param  string $action  操作
	 * @param  string $message 记录的详情
	 */
	public function info($module, $action, $message, array $data = null);

	/**
	 * 记录警告日志
	 * @param  string $module  模块
	 * @param  string $action  操作
	 * @param  string $message 记录的详情
	 */
	public function warning($module, $action, $message, array $data = null);
	

	/**
	 * 记录错误日志
	 * @param  string $module  模块
	 * @param  string $action  操作
	 * @param  string $message 记录的详情
	 */
	public function error($module, $action, $message, array $data = null);


	/**
	 * 日志搜索
	 * @param  array   $conditions 搜索条件，
	 *                 如array(
	 *                 		'level'=>'info|warning|error', 
	 *                      'nickname'=>'xxxxx',
	 *                      'startDateTime'=> 'xxxx-xx-xx xx:xx',
	 *                      'endDateTime'=> 'xxxx-xx-xx xx:xx'
	 *                 );
	 *                             
	 * @param  array   $sort       按什么排序, 暂只提供'created'
	 * @param  integer $start      开始行数
	 * @param  integer $limit      返回最多行数
	 * @return array        	   多维数组    
	 * 
	 */
	public function searchLogs($conditions, $sort, $start, $limit);

	/**
	 * 根据指定搜索条件返回该条数。
	 * @param  array   $conditions 搜索条件，
	 *                 如array(
	 *                 		'level'=>'info|warning|error', 
	 *                      'nickname'=>'xxxxx',
	 *                      'startDateTime'=> 'xxxx-xx-xx xx:xx',
	 *                      'endDateTime'=> 'xxxx-xx-xx xx:xx'
	 *                 );
	 * @return interger           
	 */
	public function searchLogCount($conditions);

	public function analysisLoginNumByTime($startTime,$endTime);

	public function analysisLoginDataByTime($startTime,$endTime);
}