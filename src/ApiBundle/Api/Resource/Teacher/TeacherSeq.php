<?php

namespace ApiBundle\Api\Resource\Teacher;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\MultiClass\MultiClassException;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class TeacherSeq extends AbstractResource
{
    /**
     * @param $id
     * @param $type
     *
     * @return bool[]
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function update(ApiRequest $request, $id, $type)
    {
        $teacher = $this->getUserService()->getUser($id);

        if (!in_array($type, ['promoted', 'cancel'])) {
            throw MultiClassException::MULTI_CLASS_DATA_FIELDS_MISSING();
        }

        if (empty($teacher) || !in_array('ROLE_TEACHER', $teacher['roles'])) {
            throw UserException::NOTFOUND_USER();
        }

        if ('promoted' == $type) {
            $number = $request->request->get('number', 0);
            $this->getUserService()->promoteUser($id, $number);
        } elseif ('cancel' == $type) {
            $this->getUserService()->cancelPromoteUser($id);
        }

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
