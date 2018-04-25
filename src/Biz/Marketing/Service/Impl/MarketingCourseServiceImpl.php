<?php

namespace Biz\Marketing\Service\Impl;

use Biz\Marketing\Service\MarketingService;

class MarketingCourseServiceImpl extends MarketingBaseServiceImpl implements MarketingService
{
    protected function joinTarget($targetId, $userId, $data)
    {
        return $this->getCourseMemberService()->becomeStudentAndCreateOrder($userId, $targetId, $data);
    }

    /**
     * 记录日志用
     * $logger->info("准备把用户,{$user['id']}添加到班级");
     */
    protected function getPreparedDebugInfo($user)
    {
        return "准备把用户,{$user['id']}添加到课程";
    }

    protected function getFinishedInfo($user, $target, $member, $order)
    {
        return "把用户,{$user['id']}添加到课程成功,课程ID：{$target['id']},memberId:{$member['id']},订单Id:{$order['id']}";
    }

    /**
     * @return MarketingCourseMemberServiceImpl
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Marketing:MarketingCourseMemberService');
    }
}
