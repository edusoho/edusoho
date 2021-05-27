<?php

namespace ApiBundle\Api\Resource\Teacher;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class TeacherPromotion extends AbstractResource
{
    /**
     * @param $id
     *
     * @return bool[]
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request, $id)
    {
        $teacher = $this->getUserService()->getUser($id);

        if (empty($teacher) || !in_array('ROLE_TEACHER', $teacher['roles'])) {
            throw UserException::NOTFOUND_USER();
        }

        $number = $request->request->get('number', 0);
        $this->getUserService()->promoteUser($id, $number);

        return ['success' => true];
    }

    /**
     * @param $id
     *
     * @return bool[]
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function remove(ApiRequest $request, $id)
    {
        $teacher = $this->getUserService()->getUser($id);

        if (empty($teacher) || !in_array('ROLE_TEACHER', $teacher['roles'])) {
            throw UserException::NOTFOUND_USER();
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
