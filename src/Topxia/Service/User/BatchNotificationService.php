<?php

namespace Topxia\Service\User;

interface BatchNotificationService
{
    public function sendBatchNotification($fromId, $title,$content,$createdTime,$targetType,$targetId,$type);

    /**
     * 获取私信内容
     */
    public function getBatchNotificationById($id);

    /**
     * 搜索特定状态下的私信条数
     *
     * @param  array $conditions 搜索条件
     * 
     * @return integer   搜索出的全站信数目
     */
    
    public function searchBatchNotificationsCount($conditions);
    /**
     * 搜索特定状态下的私信条数
     *
     * @param  array $conditions 搜索条件
     * @param  string $sort 排序方式
     * 
     * @return array   搜索出的群发私信
     */
    public function searchBatchNotifications($conditions, $sort, $start, $limit);
    /**
     * 验证用户是否有新的群发私信
     * 
     * @return integer   搜索出的全站信数目
     */
    public function checkoutBatchNotification($user);
}