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
            return $this->error('700008', '网站会话未启用');
        }

        $conversation = $this->getConversationService()->getConversationByTargetIdAndTargetType(0, 'global');

        if ($conversation) {
            if ($this->getConversationService()->isImMemberFull($imSetting['convNo'])) {
                return $this->error('700008', '会话人数已满');
            }

            $conversationMember = $this->getConversationService()->getMemberByConvNoAndUserId($conversation['no'], $user['id']);

            if (!$conversationMember) {
                $res = $this->getConversationService()->addConversationMember($imSetting['convNo'], array($user));

                if ($res) {
                    $member = array(
                        'convNo'     => $conversation['no'],
                        'targetId'   => 0,
                        'targetType' => 'global',
                        'userId'     => $user['id']
                    );

                    $this->getConversationService()->addMember($member);
                } else {
                    return $this->error('700006', '学员进入会话失败');
                }
            }

            $res = array('convNo' => $conversation['no']);
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
