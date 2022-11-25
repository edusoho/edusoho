<?php

namespace ApiBundle\Api\Resource\Teacher;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class TeacherPromotion extends AbstractResource
{
    /**
     * @param $id
     *
     * @return bool[]
     */
    public function add(ApiRequest $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2_teacher')) {
            throw new AccessDeniedException();
        }
        $teacher = $this->getUserService()->getUser($id);

        if (empty($teacher) || !in_array('ROLE_TEACHER', $teacher['roles'])) {
            $teacher = $this->getUserService()->getUserByUUID($id);
            if (empty($teacher) || !in_array('ROLE_TEACHER', $teacher['roles'])) {
                throw UserException::NOTFOUND_USER();
            }
        }

        $number = $request->request->get('number', 0);
        $this->getUserService()->promoteUser($id, $number);

        return ['success' => true];
    }

    /**
     * @param $id
     *
     * @return bool[]
     */
    public function remove(ApiRequest $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2_teacher')) {
            throw new AccessDeniedException();
        }

        $teacher = $this->getUserService()->getUser($id);

        if (empty($teacher) || !in_array('ROLE_TEACHER', $teacher['roles'])) {
            $teacher = $this->getUserService()->getUserByUUID($id);
            if (empty($teacher) || !in_array('ROLE_TEACHER', $teacher['roles'])) {
                throw UserException::NOTFOUND_USER();
            }
        }

        $this->getUserService()->cancelPromoteUser($id);

        return ['success' => true];
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
