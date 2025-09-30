<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ExtensionManager;
use Biz\Course\Service\CourseService;

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
     * @param array $arguments 参数
     *
     * @return array 用户列表
     */
    public function getData(array $arguments)
    {
        $conditions = [];
        if (isset($arguments['private']) && 0 == $arguments['private']) {
            $conditions['private'] = 0;
        }
        $orderBys = ['createdTime' => 'DESC'];

        if (isset($arguments['objectType']) && isset($arguments['objectId'])) {
            if ('course' == $arguments['objectType']) {
                $conditions['courseIds'] = [$arguments['objectId']];
            } elseif ('courseSet' == $arguments['objectType']) {
                $courses = $this->getCourseService()->findCoursesByCourseSetId($arguments['objectId']);
                $conditions['courseIds'] = ArrayToolkit::column($courses, 'id');
            } else {
                $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($arguments['objectId']);
                if ($courses) {
                    $courseIds = ArrayToolkit::column($courses, 'id');
                    $conditions['classroomCourseIds'] = $courseIds;
                    $conditions['classroomId'] = $arguments['objectId'];
                } else {
                    $conditions['onlyClassroomId'] = $arguments['objectId'];
                }
            }
        }

        if (!empty($conditions['courseIds'])) {
            $orderBys = ['createdTime' => 'DESC', 'courseId' => 'ASC'];
        }

        $statuses = $this->getStatusService()->searchStatuses($conditions, $orderBys, 0, $arguments['count']);

        if (empty($statuses)) {
            return [];
        }

        $userIds = ArrayToolkit::column($statuses, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $manager = ExtensionManager::instance();
        $courses = [];
        if (!empty($conditions['courseIds'])) {
            $courses = $this->getCourseService()->searchCourses(['ids' => $conditions['courseIds']], [], 0, PHP_INT_MAX);
            $courses = ArrayToolkit::index($courses, 'id');
        }
        foreach ($statuses as &$status) {
            $status['user'] = $this->getUserService()->hideUserNickname($users[$status['userId']]);
            $status['message'] = $manager->renderStatus($status, $arguments['mode']);
            if (!empty($courses) && 0 == $courses[$status['courseId']]['canLearn']) {
                $status['message'] = str_replace('link-dark', 'link-dark js-handleLearnContentOnMessage', $status['message']);
            }
            unset($status);
        }

        return $statuses;
    }

    protected function getStatusService()
    {
        return $this->getServiceKernel()->getBiz()->service('User:StatusService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('User:UserService');
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }
}
