<?php

namespace ApiBundle\Api\Resource\Teacher;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\TeacherQualification\Service\TeacherQualificationService;
use Biz\User\Service\UserService;

class Teacher extends AbstractResource
{
    /**
     * @return array
     */
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2')) {
            throw new AccessDeniedException();
        }

        $conditions = [
            'nickname' => $request->query->get('nickname', ''),
            'roles' => '|ROLE_TEACHER|',
            'destroyed' => 0,
            'locked' => 0,
            'excludeIds' => $request->query->get('excludeIds', []),
        ];

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $users = $this->getUserService()->searchUsers($conditions, ['createdTime' => 'DESC'], $offset, $limit);
        $users = $this->handleTeacherInfos($users);
        $total = $this->getUserService()->countUsers($conditions);

        return $this->makePagingObject($users, $total, $offset, $limit);
    }

    protected function handleTeacherInfos($users)
    {
        $users = ArrayToolkit::index($users, 'id');
        $userIds = ArrayToolkit::column($users, 'id');

        $teacherQualifications = $this->getTeacherQualificationService()->findByUserIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $userInfo = [];
        foreach ($users as $userId => $user) {
            $qualification = $teacherQualifications[$userId];
            $qualification['truename'] = $profiles[$userId]['truename'] ?: '';
            $user['qualification'] = $qualification;
            $userInfo[] = $user;
        }

        return $userInfo;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return TeacherQualificationService
     */
    private function getTeacherQualificationService()
    {
        return $this->service('TeacherQualification:TeacherQualificationService');
    }
}
