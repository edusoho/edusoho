<?php

namespace ApiBundle\Api\Resource\StudentOpenInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;

class StudentOpenInfo extends AbstractResource
{
    public function get(ApiRequest $request, $userId)
    {
        $enable = $this->getUserService()->getStudentOpenInfo($userId);

        return [
            'enable' => $enable,
        ];
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
