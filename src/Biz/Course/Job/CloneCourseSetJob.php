<?php

namespace Biz\Course\Job;

use Biz\Course\Service\CourseSetService;
use Biz\User\CurrentUser;
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
        if(!empty($user)) {
            $this->setCurrentUser($user);
        }

        $this->getCourseSetService()->cloneCourseSet($this->args['courseSetId'], $this->args['params']);
        $this->setCurrentUser($currentUser);
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
}
