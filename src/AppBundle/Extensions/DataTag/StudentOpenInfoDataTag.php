<?php

namespace AppBundle\Extensions\DataTag;

use Biz\User\Service\UserService;

class StudentOpenInfoDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $userId = $arguments['userId'];

        return  $this->getUserService()->getStudentOpenInfo($userId);
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('User:UserService');
    }
}
