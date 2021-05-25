<?php

namespace ApiBundle\Api\Resource\Teacher;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class TeacherPromote extends AbstractResource
{
    public function update(ApiRequest $request, $id, $promoteType)
    {
        $teacher = $this->getUserService()->getUser($id);

        if (empty($teacher) || !in_array('ROLE_TEACHER', $teacher['roles'])) {
            throw UserException::NOTFOUND_USER();
        }

        if ('promoted' == $promoteType) {
            $number = $request->request->get('number', 0);
            $this->getUserService()->promoteUser($id, $number);
        } elseif ('cancel' == $promoteType) {
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
