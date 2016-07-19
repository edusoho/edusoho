<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class Conversations extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $requiredFields = array('memberIds');
        $fields = $this->checkRequiredFields($requiredFields, $request->request->all());

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
            }

            $message = array(
                'name' => '',
                'clients' => array(),
            );
            foreach ($users as $user) {
                $message['name'] .= $user['nickname'] . '-';
                $message['clients'][] = array(
                    'clientId' => $user['id'],
                    'clientName' => $user['nickname'],
                );
            }
            $message['name'] = rtrim($message['name'], '-');

            //@todo leaf
            $resp = CloudAPIFactory::create('root')->post('/im/me/conversation', $message);
            if (isset($resp['error'])) {
                $resp['code'] = isset($resp['code']) ? $resp['code'] : 500;
                return $this->error($resp['code'], $resp['error']);
            }
            $conversationNo = $resp['no'];

            //保存云端conversationNo到本地
            $conversation = $this->getConversationService()->addConversation(array(
                'no' => $conversationNo,
                'memberIds' => $memberIds,
            ));

            //创建用户各自的会话
            foreach ($users as $user) {
                $this->getConversationService()->addMyConversation(array(
                    'no' => $conversationNo,
                    'userId' => $user['id'],
                ));
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
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }
}
