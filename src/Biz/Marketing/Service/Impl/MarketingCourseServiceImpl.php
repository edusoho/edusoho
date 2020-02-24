<?php

namespace Biz\Marketing\Service\Impl;

use Biz\Course\MemberException;
use Biz\Course\Service\CourseService;
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

    protected function getFinishedInfo($user, $target, $member, $order, $hasJoined)
    {
        if ($hasJoined) {
            $prefixLogInfo = "用户,{$user['id']}已经是课程成员";
        } else {
            $prefixLogInfo = "把用户,{$user['id']}添加到课程成功";
        }

        return $prefixLogInfo.",课程ID：{$target['id']},memberId:{$member['id']},订单Id:{$order['id']}";
    }

    protected function getProduct($targetId)
    {
        return $this->getCourseService()->getCourse($targetId);
    }

    protected function getMember($targetId, $userId)
    {
        return $this->getCourseMemberService()->getCourseMember($targetId, $userId);
    }

    protected function createMarketingOrder($targetId, $userId, $data)
    {
        return $this->getCourseMemberService()->createMarketingOrder($targetId, $userId, $data);
    }

    protected function getDuplicateJoinCode()
    {
        return MemberException::DUPLICATE_MEMBER;
    }

    /**
     * @return MarketingCourseMemberServiceImpl
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Marketing:MarketingCourseMemberService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
