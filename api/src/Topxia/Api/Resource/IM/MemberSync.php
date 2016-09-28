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

        if (!isset($imSetting['enabled']) || !$imSetting['enabled']) {
            $res = $this->error('700008', '网站会话未启用');
        }

        $conversationMember = $this->getConversationService()->getMemberByConvNoAndUserId($imSetting['convNo'], $user['id']);

        if (!empty($imSetting['convNo']) && !$conversationMember) {
            if ($this->getConversationService()->isImMemberFull()) {
                return $this->error('700008', '会话人数已满');
            }

            $res = $this->getConversationService()->addConversationMember($imSetting['convNo'], $user['id'], $user['nickname']);

            if ($res) {
                $member = array(
                    'convNo'     => $imSetting['convNo'],
                    'targetId'   => 0,
                    'targetType' => 'global',
                    'userId'     => $user['id']
                );

                $conversationMember = $this->getConversationService()->addMember($member);
                $res                = array('convNo' => $imSetting['convNo']);
            } else {
                $res = $this->error('700006', '学员进入会话失败');
            }
        } elseif ($conversationMember) {
            $res = array('convNo' => $conversationMember['convNo']);
        }

        return $res;
    }

    public function filter($res)
    {
        return $res;
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
