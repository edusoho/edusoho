<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\UserException;

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

    public function add(ApiRequest $request)
    {
        $userId = $request->request->get('userId');

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw UserException::NOTFOUND_USER();
        }

        $friend = $this->getUserService()->follow($this->getCurrentUser()->id, $userId);

        return array(
            'success' => empty($friend) ? false : true,
        );
    }

    public function remove(ApiRequest $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw UserException::NOTFOUND_USER();
        }

        $friend = $this->getUserService()->unFollow($this->getCurrentUser()->id, $userId);

        return array(
            'success' => empty($friend) ? false : true,
        );
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
