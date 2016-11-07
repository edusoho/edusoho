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

        $this->syncClassroomConversations($user);
        $this->syncCourseConversations($user);

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

    protected function syncCourseConversations($user)
    {
        $count   = $this->getCourseService()->findUserLearnCourseCount($user['id']);
        $courses = $this->getCourseService()->findUserLearnCourses($user['id'], 0, $count);

        $courseIds = ArrayToolkit::column($courses, 'courseId');
        $courseMap = ArrayToolkit::index($courses, 'courseId');

        return $this->syncTargetConversations($user, $courseMap, 'course')
        && $this->syncCourseConversationMembers($user, $courseIds);
    }

    protected function syncClassroomConversations($user)
    {
        $classroomIds = $this->getClassroomService()->findUserJoinedClassroomIds($user['id']);
        $classrooms   = $this->getClassroomService()->findClassroomsByIds($classroomIds);
        $classroomMap = ArrayToolkit::index($classrooms, 'classroomId');

        return $this->syncTargetConversations($user, $classroomMap, 'classroom')
        && $this->syncClassroomConversationMember($user, $classroomIds);
    }

    protected function syncCourseConversationMembers($user, $courseIds)
    {
        $convMembers = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], 'course');

        if (!$convMembers) {
            return false;
        }

        foreach ($convMembers as $convMember) {
            if (!in_array($convMember['targetId'], $courseIds)) {
                $this->getConversationService()->quitConversation($convMember['convNo'], $convMember['userId']);
            }
        }

        return true;
    }

    protected function syncClassroomConversationMember($user, $classroomIds)
    {
        $convMembers = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], 'classroom');

        if (!$convMembers) {
            return false;
        }

        foreach ($convMembers as $convMember) {
            if (!in_array($convMember['targetId'], $classroomIds)) {
                $this->getConversationService()->quitConversation($convMember['convNo'], $convMember['userId']);
            }
        }

        return true;
    }

    protected function syncTargetConversations($user, $targetMap, $targetType)
    {
        $userConvs = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], $targetType.'-push');
        if (empty($targetMap)) {
            foreach ($userConvs as $uc) {
                $this->getConversationService()->quitConversation($uc['convNo'], $user['id']);
            }
            return;
        }
        $userConvsMap = ArrayToolkit::index($userConvs, 'targetId');
        foreach ($targetMap as $csKey => $csVal) {
            if (!isset($userConvsMap[$csKey])) {
                $this->getConversationService()->joinConversation($userConvsMap[$csKey]['convNo'], $user['id']);
            }
        }
        foreach ($userConvsMap as $ucKey => $ucVal) {
            if (!isset($targetMap[$ucKey])) {
                $this->getConversationService()->quitConversation($userConvsMap[$ucKey]['convNo'], $user['id']);
            }
        }

        $targetConvs = $this->getConversationService()->searchConversations(array(
            'targetIds'   => array_keys($targetMap),
            'targetTypes' => array($targetType.'-push')
        ));
        $targetConvsMap = ArrayToolkit::index($targetConvs, 'targetId');

        foreach ($targetConvsMap as $convKey => $convVal) {
            if (!isset($targetMap[$convKey])) {
                $conv = $this->getConversationService()->createConversation('推送：'.$targetsMap[$convKey]['title'], $targetType.'-push', $convKey, array($user));
            }
        }
        foreach ($targetMap as $csKey => $csVal) {
            if (!isset($targetConvsMap[$csKey])) {
                $this->getConversationService()->removeConversation($targetConvsMap[$csKey]['convNo']);
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
