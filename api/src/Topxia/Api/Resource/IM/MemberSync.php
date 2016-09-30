<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;

class MemberSync extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $imSetting = $this->getSettingservice()->get('app_im', array());
        $user      = $this->getCurrentUser();
        $res       = $this->error('700007', '全站会话未创建');

        if (empty($imSetting['enabled'])) {
            return $this->error('700008', '网站会话未启用');
        }

        $conversation = $this->getConversationService()->getConversationByTargetIdAndTargetType(0, 'global');

        if ($conversation) {
            $conversationMember = $this->getConversationService()->getMemberByConvNoAndUserId($conversation['no'], $user['id']);

            if (!$conversationMember) {
                return $this->joinCoversationMember($conversation['no'], 0, 'global', $user);
            }

            $res = array('convNo' => $conversation['no']);
        }

        return $res;
    }

    public function filter($res)
    {
        return $res;
    }

    protected function joinCoversationMember($convNo, $targetId, $targetType, $user)
    {
        $res = $this->getConversationService()->addConversationMember($convNo, $user['id'], $user['nickname']);

        if ($res) {
            $member = array(
                'convNo'     => $convNo,
                'targetId'   => $targetId,
                'targetType' => $targetType,
                'userId'     => $user['id']
            );
            $this->getConversationService()->addMember($member);

            return array('convNo' => $convNo);
        } else {
            return $this->error('700006', '学员进入会话失败');
        }
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
