<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\UserException;

class UserSync extends AbstractResource
{
    public function search(ApiRequest $request, $id)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        $users = $this->getUserService()->searchUsers(['id_GT' => $id], ['id' => 'ASC'], $offset, $limit);
        $total = $this->getUserService()->countUsers(['id_GT' => $id]);

        return $this->makePagingObject($users, $total, $offset, $limit);
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
