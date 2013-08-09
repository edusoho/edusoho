<?php

namespace Topxia\Service\User;

interface NotificationService
{
	/**
	 * 给用户发送通知
	 * 
	 * @param  integer 		$userId  通知接收方用户ID
	 * @param  string 	$type    通知类型
	 * @param  mixed 	$content 通知内容，可以为string，array。
	 */
    public function notify($userId, $type, $content);

    /**
     * 获得用户最新的通知
     * 
     * @param  integer 	$userId 用户ID
     * @param  integer 	$start  取通知记录的开始行数
     * @param  integer 	$limit  取通知记录的行数
     * 
     * @return array 用户最新的通知
     */
    public function findUserNotifications($userId, $start, $limit);

    public function getUserNotificationCount($userId);

    public function clearUserNewNotificationCounter($userId);
}