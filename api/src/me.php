<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

$api = $app['controllers_factory'];

$api->get('/notifications', function (Request $request) {
    $user = getCurrentUser();
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $type = $request->query->get('type', '');
    $conditions['userId'] = $user['id'];

    if (!empty($type)) {
        $conditions['type'] = $type;
    }

    $notifications = ServiceKernel::instance()->createService('User:NotificationService')->searchNotifications(
        $conditions,
        array('createdTime' => 'DESC'),
        $start,
        $limit
    );
    $count = ServiceKernel::instance()->createService('User:NotificationService')->countNotifications($conditions);
    return array(
        'data' => filters($notifications, 'notification'),
        'total' => (string) $count,
    );
}

);

return $api;
