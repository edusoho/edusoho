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
        $imSetting = $this->getSettingservice()->get('app_im', array());
        $user      = $this->getCurrentUser();
        $res       = $this->error('700007', '全站会话未创建');

        if (empty($imSetting['enabled'])) {
            return $this->error('700008', '网站会话未启用');
        }

        $conversation = $this->getConversationService()->getConversationByTarget(0, 'global');

        if ($conversation) {
            $conversationMember = $this->getConversationService()->getMemberByConvNoAndUserId($conversation['no'], $user['id']);

            if (!$conversationMember) {
                return $this->joinCoversationMember($conversation['no'], 0, 'global', $user);
            }

            $res = array('convNo' => $conversation['no']);
        }

        $this->courseConversationMemberSync();
        $this->classroomConversationMemberSync();

        return $res;
    }

    public function filter($res)
    {
        return $res;
    }

    protected function joinCoversationMember($convNo, $targetId, $targetType, $user)
    {
        $res = $this->getConversationService()->addConversationMember($convNo, array($user));

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

    protected function courseConversationMemberSync()
    {
        $user = $this->getCurrentUser();

        $memberCourses = $this->getCourseService()->searchMembers(
            array('userId' => $user['id'], 'joinedType' => 'course'),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        if (!$memberCourses) {
            return false;
        }

        $memberCourses = ArrayToolkit::index($memberCourses, 'courseId');

        $memberConversations = $this->getConversationService()->searchImMembers(
            array(
                'userId'     => $user['id'],
                'targetType' => 'course'
            ),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        if (!$memberConversations) {
            return false;
        }

        foreach ($memberConversations as $conversation) {
            if (isset($memberCourses[$conversation['targetId']])) {
                continue;
            } else {
                $this->getConversationService()->deleteMember($conversation['id']);
            }
        }

        return true;
    }

    protected function classroomConversationMemberSync()
    {
        $user = $this->getCurrentUser();

        $memberClassrooms = $this->getClassroomService()->searchMembers(
            array('userId' => $user['id']),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        if (!$memberClassrooms) {
            return false;
        }

        $memberClassrooms = ArrayToolkit::index($memberClassrooms, 'classroomId');

        $memberConversations = $this->getConversationService()->searchImMembers(
            array(
                'userId'     => $user['id'],
                'targetType' => 'classroom'
            ),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        if (!$memberConversations) {
            return false;
        }

        foreach ($memberConversations as $conversation) {
            if (isset($memberClassrooms[$conversation['targetId']])) {
                continue;
            } else {
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
