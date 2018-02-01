<?php

namespace Biz\Marketing\Service\Impl;

use Biz\Marketing\Service\MarketingService;

class MarketingClassroomServiceImpl extends MarketingBaseServiceImpl implements MarketingService
{
    protected function joinTarget($targetId, $userId, $data)
    {
        return $this->getClassroomMemberService()->becomeStudentWithOrder($targetId, $userId, $data);
    }

    /**
     * 记录debug日志用
     * $logger->debug("准备把用户,{$user['id']}添加到班级");
     */
    protected function getPreparedDebugInfo($user)
    {
        return "准备把用户,{$user['id']}添加到班级";
    }

    protected function getFinishedInfo($user, $target, $member, $order)
    {
        return "把用户,{$user['id']}添加到班级成功,班级ID：{$target['id']},memberId:{$member['id']},订单Id:{$order['id']}";
    }

    protected function getTargetType()
    {
        return 'MarketingClassroom';
    }

    /**
     * @return MarketingClassroomMemberServiceImpl
     */
    protected function getClassroomMemberService()
    {
        return $this->createService('Marketing:MarketingClassroomMemberService');
    }
}
