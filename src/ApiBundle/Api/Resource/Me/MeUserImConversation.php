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

    public function post(Application $app, Request $request)
    {
        $requiredFields = array('memberIds');
        $fields = $this->checkRequiredFields($requiredFields, $request->request->all());

        $memberIds = explode(',', $fields['memberIds']);

        if (2 != count($memberIds)) {
            return $this->error(500, "Only support memberIds's count of 2");
        }

        $conversation = $this->getConversationService()->getConversationByMemberIds($memberIds);

        if (empty($conversation)) {
            $users = $this->getUserService()->findUsersByIds($memberIds);

            foreach ($memberIds as $memberId) {
                if (!in_array($memberId, ArrayToolkit::column($users, 'id'))) {
                    return $this->error(500, "User #{$memberId} is not exsit");
                }
                $user['id'] = $users[$memberId]['id'];
                $user['nickname'] = $users[$memberId]['nickname'];

                $members[] = $user;
            }

            try {
                $conversation = $this->getConversationService()->createConversation('', 'private', 0, $members);
            } catch (\Exception $e) {
                return $this->error($e->getCode(), $e->getMessage());
            }
        }

        return $this->filter($conversation);
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
