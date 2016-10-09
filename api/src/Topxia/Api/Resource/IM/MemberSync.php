<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;

class MemberSync extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $setting = $this->getSettingservice()->get('app_im', array());
        if (empty($setting['enabled'])) {
            return $this->error('700008', '网站会话未启用');
        }

        $user = $this->getCurrentUser();

        $this->syncCourseConversationMembers($user);
        $this->syncClassroomConversationMember($user);

        return $this->joinGlobalConversation($user);
    }

    public function filter($res)
    {
        return $res;
    }

    protected function joinGlobalConversation($user)
    {
        $user = $this->getCurrentUser();

        $conv = $this->getConversationService()->getConversationByTarget(0, 'global');
        if (!$conv) {
            return $this->error('700007', '全站会话未创建');;
        }

        $convMember = $this->getConversationService()->getMemberByConvNoAndUserId($conv['no'], $user['id']);
        if ($convMember) {
            return array('convNo' => $conv['no']);
        }

        //joinConversation($convNo, $userId);
        $added = $this->getConversationService()->addConversationMember($convNo, array($user));
        if (!$added) {
            return $this->error('700006', '学员进入会话失败');
        }

        $member = array(
            'convNo'     => $convNo,
            'targetId'   => $targetId,
            'targetType' => $targetType,
            'userId'     => $user['id']
        );
        $this->getConversationService()->addMember($member);

        return array('convNo' => $convNo);
    }

    protected function syncCourseConversationMembers($user)
    {
        $user = $this->getCurrentUser();

        // findMembersByUserIdAndTargetType($userId, $targetType);
        $convMembers = $this->getConversationService()->searchMembers(
            array(
                'userId'     => $user['id'],
                'targetType' => 'course'
            ),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        if (!$convMembers) {
            return false;
        }

        // findUserJoinedCourseIds($userId, $joinedType = 'course');
        $courseMembers = $this->getCourseService()->searchMembers(
            array('userId' => $user['id'], 'joinedType' => 'course'),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );
        $courseIds = ArrayToolkit::column($courseMembers, 'courseId');

        foreach ($convMembers as $convMember) {
            if (!in_array($convMember['targetId'], $courseIds)) {
                $this->getConversationService()->deleteMember($convMember['id']);
            }
        }

        return true;
    }

    protected function syncClassroomConversationMember($user)
    {
        $conversationMembers = $this->getConversationService()->searchMembers(
            array(
                'userId'     => $user['id'],
                'targetType' => 'classroom'
            ),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        if (!$conversationMembers) {
            return false;
        }

        $classroomMembers = $this->getClassroomService()->searchMembers(
            array('userId' => $user['id']),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        $classroomIds = ArrayToolkit::index($classroomMembers, 'classroomId');

        foreach ($conversationMembers as $conversationMember) {
            if (!in_array($conversationMember['targetId'], $classroomIds)) {
                $this->getConversationService()->deleteMember($conversation['id']);
            }
        }

        return true;
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
