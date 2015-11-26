<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\Common\ExtensionManager;
use Topxia\WebBundle\Extensions\DataTag\DataTag;

class LastestNotificationsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取所有用户的最新动态
     *
     * 可传入的参数：
     *   mode     必需 动态的模式(simple, full)
     *   count    必需 获取动态数量
     *   objectType 可选 动态所属对象类型
     *   objectId   可选 动态所属对象编号
     *
     * @param  array $arguments     参数
     * @return array 用户列表
     */
    public function getData(array $arguments)
    {
        $notifications = $arguments['notifications'];

        if ($notifications) {
            $manager = ExtensionManager::instance();

            foreach ($notifications as &$notification) {
                $notification['message'] = $manager->renderNotifications($notification);
                unset($notification);
            }
        }

        return $notifications;
    }

    protected function getStatusService()
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
