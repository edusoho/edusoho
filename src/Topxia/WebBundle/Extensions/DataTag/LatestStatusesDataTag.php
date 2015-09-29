<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\ExtensionManager;

class LatestStatusesDataTag extends BaseDataTag implements DataTag  
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
     * @param  array $arguments 参数
     * @return array 用户列表
     */
    public function getData(array $arguments)
    {   
        $conditions = array();
        if(isset($arguments['private'])){
            if($arguments['private'] == 0){
                $conditions['private'] = 0;
            }
        }
        if (isset($arguments['objectType']) && isset($arguments['objectId'])) {
            if ($arguments['objectType'] == 'course') {
                $conditions['courseIds'] = array($arguments['objectId']);
            } else {
                $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);

                if ($courses) {
                    $courseIds = ArrayToolkit::column($courses, 'id');
                    $conditions['classroomCourseIds'] = $courseIds;
                    $conditions['classroomId'] = $arguments['objectId'];
                } else {
                    $conditions['onlyClassroomId'] = $arguments['objectId'];
                }
                
            }
        }

        
        $statuses = $this->getStatusService()->searchStatuses($conditions, array('createdTime', 'DESC'), 0, $arguments['count']);

        if ($statuses) {
            $userIds = ArrayToolkit::column($statuses, 'userId');
            $users = $this->getUserService()->findUsersByIds($userIds);

            $manager = ExtensionManager::instance();

            foreach ($statuses as &$status) {
                $status['user'] = $users[$status['userId']];
                $status['message'] = $manager->renderStatus($status, $arguments['mode']);
                unset($status);
            }
        }
        

        return $statuses;
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
