<?php

namespace Biz\Course\Job;

use Biz\Course\Service\CourseSetService;
use Biz\User\CurrentUser;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Biz\Role\Util\PermissionBuilder;

class CloneCourseSetJob extends AbstractJob
{
    public function execute()
    {
        $currentUser = $this->biz['user'];
        $userId = $this->args['userId'];
        $user = $this->getUserService()->getUser($userId);
        $user['currentIp'] = '127.0.0.1';
        if (!empty($user)) {
            $this->setCurrentUser($user);
        }

        $courseSetId = $this->args['courseSetId'];
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $params = $this->args['params'];
        $originTitle = $courseSet['title'];
        $newTitle = $params['title'];
        $message = array(
            'newTitle' => $newTitle,
            'originTitle' => $originTitle,
        );

        try {
            $this->getCourseSetService()->cloneCourseSet($courseSetId, $params);
            $message['status'] = 'success';
            $this->getNotificationService()->notify($userId, 'course-copy', $message);
        } catch (\Exception $e) {
            $message['status'] = 'failure';
            $this->getNotificationService()->notify($userId, 'course-copy', $message);
        }

        $this->setCurrentUser($currentUser->toArray());
    }

    private function setCurrentUser($user)
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $this->biz['user'] = $currentUser;
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return NotificationService
     */
    private function getNotificationService()
    {
        return $this->biz->service('User:NotificationService');
    }
}
