<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class MeIMConversation extends BaseResource
{
    public function post(Application $app, Request $request, $toUserId)
    {
        $user = $this->getCurrentUser();

        //先查本地数据库是否有这两人的会话记录
        $conversation = $this->getIMConversationService()->getConversationByUserIds(array($user['id'], $toUserId));

        if (empty($conversation)) {
            $toUser = $this->getUserService()->getUser($toUserId);
            if (empty($toUser)) {
                return $this->error(500, "ID为{$toUserId}的用户不存在");
            }
            $message = array(
                'name'   => "{$user['nickname']} - {$toUser['nickname']}",
                'clients' => array(
                        array('clientId' => $user['id'], 'clientName' => $user['nickname']),
                        array('clientId' => $toUser['id'], 'clientName' => $toUser['nickname'])
                    )
            );
            $resp = CloudAPIFactory::create('leaf')->post('/im/me/conversation', $message);
            if (isset($resp['error'])) {
                $resp['code'] = isset($resp['code']) ? $resp['code'] : 500;
                return $this->error($resp['code'], $resp['error']);
            }

            //保存云端conversationNo到本地
            $conversation = $this->getIMConversationService()->addConversation(array(
                'conversationNo' => $resp['no'],
                'userIds' => array($user['id'], $toUser['id']),
                'createdTime' => time(),
            ));
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

    protected function getIMConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }
}
