<?php

namespace Biz\Marketing\Service\Impl;

use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\Marketing\Service\MarketingService;

class MarketingClassroomServiceImpl extends MarketingBaseServiceImpl implements MarketingService
{
    protected function joinTarget($targetId, $userId, $data)
    {
        return $this->getClassroomMemberService()->becomeStudentWithOrder($targetId, $userId, $data);
    }

    /**
     * 记录日志用
     * $logger->info("准备把用户,{$user['id']}添加到班级");
     */
    protected function getPreparedDebugInfo($user)
    {
        return "准备把用户,{$user['id']}添加到班级";
    }

    protected function getFinishedInfo($user, $target, $member, $order, $hasJoined)
    {
        if ($hasJoined) {
            $prefixLogInfo = "用户,{$user['id']}已经是班级成员";
        } else {
            $prefixLogInfo = "把用户,{$user['id']}添加到班级成功";
        }

        return $prefixLogInfo.",班级ID：{$target['id']},memberId:{$member['id']},订单Id:{$order['id']}";
    }

    protected function getProduct($targetId)
    {
        return $this->getClassroomService()->getClassroom($targetId);
    }

    protected function getMember($targetId, $userId)
    {
        return $this->getClassroomMemberService()->getClassroomMember($targetId, $userId);
    }

    protected function createMarketingOrder($targetId, $userId, $data)
    {
        return $this->getClassroomMemberService()->createMarketingOrder($targetId, $userId, $data);
    }

    protected function getDuplicateJoinCode()
    {
        return ClassroomException::DUPLICATE_JOIN;
    }

    /**
     * @return MarketingClassroomMemberServiceImpl
     */
    protected function getClassroomMemberService()
    {
        return $this->createService('Marketing:MarketingClassroomMemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
