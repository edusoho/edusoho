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
        $courses    = $this->getCourseService()->findUserLearnCourses($user['id'], 0, 1000);
        $coursesMap = ArrayToolkit::index($courses, 'id');
        return $this->syncTargetConversations($user, $coursesMap, 'course');

        return true;
    }

    protected function syncClassroomConversations($user)
    {
        $classroomIds = $this->getClassroomService()->findUserJoinedClassroomIds($user['id'], 0, 1000);
        return $this->syncTargetConversations($user, $classroomIds, 'classroom');

        return true;
    }

    protected function syncTargetConversations($user, $targetIds, $targetType)
    {
        if (empty($targetIds)) {
            return;
        }

        $targetConvs = $this->getConversationService()->searchConversations(array(
            'targetIds'   => $targetIds,
            'targetTypes' => array($targetType.'-push')
        ));
        $targetConvsMap = ArrayToolkit::index($targetConvs, 'targetId');

        foreach ($targetConvsMap as $convKey => $convVal) {
            if (!isset($targetsMap[$convKey])) {
                $conv = $this->getConversationService()->createConversation($targetsMap[$convKey]['title'], $targetType.'-push', $convKey, array($user));
            }
        }
        foreach ($targets as $csKey => $csVal) {
            if (!isset($targetConvsMap[$csKey])) {
                $this->getConversationService()->removeConversation($targetConvsMap[$csKey]['convNo']);
            }
        }

        $userConvs    = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], $targetType.'-push');
        $userConvsMap = ArrayToolkit::index($userConvs, 'targetId');
        foreach ($targets as $csKey => $csVal) {
            if (!isset($userConvsMap[$csKey])) {
                $this->getConversationService()->joinConversation($targetConvsMap[$ucKey]['convNo'], $user['id']);
            }
        }
        foreach ($userConvsMap as $ucKey => $ucVal) {
            if (!isset($targets[$ucKey])) {
                $this->getConversationService()->quitConversation($userConvsMap[$csKey]['convNo'], $user['id']);
            }
        }

        $userConvs2    = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], 'target');
        $userConvs2Map = ArrayToolkit::index($userConvs2, 'targetId');
        foreach ($targets as $csKey => $csVal) {
            if (!isset($userConvs2Map[$csKey])) {
                $this->getConversationService()->joinConversation($targetConvs2Map[$ucKey]['convNo'], $user['id']);
            }
        }
        foreach ($userConvs2Map as $ucKey => $ucVal) {
            if (!isset($targets[$ucKey])) {
                $this->getConversationService()->quitConversation($userConvs2Map[$csKey]['convNo'], $user['id']);
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
