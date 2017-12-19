<?php

namespace ApiBundle\Api\Resource\Conversation;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use AppBundle\Common\ArrayToolkit;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;

class Conversation extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $conversations = $this->getMessageService()->findNewUserConversations($user->id, 0, 5);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($conversations, 'fromId'));
        return $this->renderView("ApiBundle:message:user-inform-message.html.twig", array(
            'conversations' => $conversations,
            'users' => $users,
        ));
    }

    protected function getMessageService()
    {
        return $this->service('User:MessageService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}