<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class MeFriend extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\User\UserFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();

        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $friends = $this->getUserService()->findFriends($user['id'], $offset, $limit);

        $total = $this->getUserService()->findFriendCount($user['id']);

        return $this->makePagingObject(array_values($friends), $total, $offset, $limit);
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
