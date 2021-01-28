<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class Conversations extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $requiredFields = array('memberIds');
        $fields         = $this->checkRequiredFields($requiredFields, $request->request->all());

        $memberIds = explode(',', $fields['memberIds']);

        if (count($memberIds) != 2) {
            return $this->error(500, "Only support memberIds's count of 2");
        }

        $conversation = $this->getConversationService()->getConversationByMemberIds($memberIds);

        if (empty($conversation)) {
            $users = $this->getUserService()->findUsersByIds($memberIds);

            foreach ($memberIds as $memberId) {
                if (!in_array($memberId, ArrayToolkit::column($users, 'id'))) {
                    return $this->error(500, "User #{$memberId} is not exsit");
                }
                $user['id']       = $users[$memberId]['id'];
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

    public function filter($res)
    {
        return $res;
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM:ConversationService');
    }
}
