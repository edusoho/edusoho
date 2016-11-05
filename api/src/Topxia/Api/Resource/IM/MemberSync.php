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
        $conv = $this->getConversationService()->getConversationByTarget(0, 'global');
        if (!$conv) {
            return $this->error('700007', '全站会话未创建');
        }

        $convMember = $this->getConversationService()->getMemberByConvNoAndUserId($conv['no'], $user['id']);
        if ($convMember) {
            return array('convNo' => $convMember['convNo']);
        }

        try {
            $convMember = $this->getConversationService()->joinConversation($conv['no'], $user['id']);
            return array('convNo' => $convMember['convNo']);
        } catch (\Exception $e) {
            return $this->error($e->getCode(), $e->getMessage());
        }
    }

    protected function syncCourseConversationMembers($user)
    {
        $convMembers = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], 'course');

        if (!$convMembers) {
            return false;
        }

        $courseIds = $this->getCourseService()->findUserJoinedCourseIds($user['id'], $joinedType = 'course');
        $courseIds = ArrayToolkit::column($courseIds, 'courseId');

        foreach ($convMembers as $convMember) {
            if (!in_array($convMember['targetId'], $courseIds)) {
                $this->getConversationService()->quitConversation($convMember['convNo'], $convMember['userId']);
            }
        }

        return true;
    }

    protected function syncClassroomConversationMember($user)
    {
        $convMembers = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], 'classroom');

        if (!$convMembers) {
            return false;
        }

        $classroomIds = $this->getClassroomService()->findUserJoinedClassroomIds($user['id']);
        $classroomIds = ArrayToolkit::column($classroomIds, 'classroomId');

        foreach ($convMembers as $convMember) {
            if (!in_array($convMember['targetId'], $classroomIds)) {
                $this->getConversationService()->quitConversation($convMember['convNo'], $convMember['userId']);
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
