<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class MeFriendship extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();

        $toIds = $request->query->get('toIds');

        $friendships = array();

        if (empty($toIds)) {
            return $friendships;
        }

        foreach ($toIds as $toId) {
            $toUser = $this->getUserService()->getUser($toId);

            if (empty($toUser)) {
                $friendships[] = 'no-user';
                continue;
            }

            $follwers = $this->getUserService()->findAllUserFollower($user['id']);

            $follwings = $this->getUserService()->findAllUserFollowing($user['id']);

            $toId = $toUser['id'];

            if (!empty($follwers[$toId])) {
                $friendships[] = !empty($follwings[$toId]) ? 'friend' : 'follower';
            } else {
                $friendships[] = !empty($follwings[$toId]) ? 'following' : 'none';
            }
        }

        return $friendships;
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
