<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\ApiRequest;
use Biz\User\UserException;
use Biz\IM\ConversationException;
use ApiBundle\Api\Annotation\ResponseFilter;

class MeUserImConversation extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Conversation\ConversationFilter", mode="public")
     */
    public function add(ApiRequest $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw UserException::NOTFOUND_USER();
        }

        $currentUser = $this->getCurrentUser();

        $memberIds = array($user['id'], $currentUser['id']);

        $conversation = $this->getConversationService()->getConversationByMemberIds($memberIds);

        if (empty($conversation)) {
            try {
                $conversation = $this->getConversationService()->createConversation('', 'private', 0, array(
                    $user,
                    array('id' => $currentUser['id'], 'nickname' => $currentUser['nickname']),
                ));
            } catch (\Exception $e) {
                throw ConversationException::JOIN_FAILED();
            }
        }

        return array(
            'convNo' => $conversation['no'],
            'user' => $user,
        );
    }

    protected function getConversationService()
    {
        return $this->service('IM:ConversationService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
