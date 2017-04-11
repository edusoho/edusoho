<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Topxia\Api\Resource\BaseResource;
use Biz\System\Service\SettingService;
use Biz\IM\Service\ConversationService;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;

class MemberSync extends BaseResource
{
    /*每次请求允许创建的最大会话数量*/
    const MAX_CREATION_PER_TIME = 20;

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
        $courseIds = $this->getCourseMemberService()->findMembersByUserIdAndJoinType($user['id']);

        $this->syncTargetConversations($user, $courseIds, 'course');
        $this->syncCourseConversationMembers($user, $courseIds);
    }

    protected function syncClassroomConversations($user)
    {
        $classroomIds = $this->getClassroomService()->findUserJoinedClassroomIds($user['id']);
        $classroomIds = ArrayToolkit::column($classroomIds, 'classroomId');

        $this->syncTargetConversations($user, $classroomIds, 'classroom');
        $this->syncClassroomConversationMembers($user, $classroomIds);
    }

    protected function syncCourseConversationMembers($user, $courseIds)
    {
        $convMembers = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], 'course');

        if (!$convMembers) {
            return;
        }

        foreach ($convMembers as $convMember) {
            if (!in_array($convMember['targetId'], $courseIds)) {
                $this->addDebug(
                    'MemberSync',
                    'syncCourseConversationMembers quitConversation : convNo='.$convMember['convNo'].',targetId='.$convMember['targetId']
                );
                $this->getConversationService()->quitConversation($convMember['convNo'], $convMember['userId']);
            }
        }
    }

    protected function syncClassroomConversationMembers($user, $classroomIds)
    {
        $convMembers = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], 'classroom');

        if (!$convMembers) {
            return;
        }

        foreach ($convMembers as $convMember) {
            if (!in_array($convMember['targetId'], $classroomIds)) {
                $this->addDebug(
                    'MemberSync',
                    'syncClassroomConversationMembers quitConversation : convNo='.$convMember['convNo'].',targetId='.$convMember['targetId']
                );
                $this->getConversationService()->quitConversation($convMember['convNo'], $convMember['userId']);
            }
        }
    }

    protected function syncTargetConversations($user, $targetIds, $targetType)
    {
        $userConvs = $this->getConversationService()->findMembersByUserIdAndTargetType(
            $user['id'],
            $targetType.'-push'
        );

        $userConvIds = ArrayToolkit::column($userConvs, 'targetId');

        $this->joinConversations(array_diff($targetIds, $userConvIds), $targetType, $user);
        $this->quitConversations(array_diff($userConvIds, $targetIds), $userConvs, $user);
    }

    protected function joinConversations($targetIds, $targetType, $user)
    {
        $params = array(
            'targetIds' => $targetIds,
            'targetTypes' => array($targetType.'-push'),
        );
        $count = $this->getConversationService()->searchConversationCount($params);
        $targetConvs = $this->getConversationService()->searchConversations(
            $params,
            array('createdTime' => 'DESC'),
            0,
            $count
        );

        $targetConvsMap = ArrayToolkit::index($targetConvs, 'targetId');
        $targetConvIds = ArrayToolkit::column($targetConvs, 'targetId');

        $toCreate = array_diff($targetIds, $targetConvIds);

        $cnt = 0;
        foreach ($targetIds as $id) {
            if (in_array($id, $toCreate)) {
                // 防止请求过于频繁造成服务器压力过大
                $cnt += 1;
                if ($cnt > self::MAX_CREATION_PER_TIME) {
                    break;
                }
                $this->addDebug(
                    'MemberSync',
                    'joinConversations & create : targetType='.$targetType.', targetId='.$id.', userId='.$user['id']
                );
                $this->getConversationService()->createConversation(
                    '推送：'.$this->getTargetTitle($id, $targetType),
                    $targetType.'-push',
                    $id,
                    array($user)
                );
            } else {
                if (!isset($targetConvsMap[$id])) {
                    continue;
                }
                $this->addDebug(
                    'MemberSync',
                    'joinConversations & join : targetType='.$targetType.',convNo='.$targetConvsMap[$id]['no'].',targetId='.$id
                );
                $this->getConversationService()->joinConversation($targetConvsMap[$id]['no'], $user['id']);
            }
        }
    }

    protected function quitConversations($targetIds, $userConvs, $user)
    {
        $userConvsMap = ArrayToolkit::index($userConvs, 'targetId');
        foreach ($targetIds as $id) {
            try {
                $this->addDebug(
                    'MemberSync',
                    'quitConversations : targetType='.$userConvsMap[$id]['targetType'].',convNo='.$userConvsMap[$id]['convNo'].',targetId='.$id
                );
                $this->getConversationService()->quitConversation($userConvsMap[$id]['convNo'], $user['id']);
            } catch (\Exception $e) {
                $this->addError(
                    'MemberSync',
                    'quitConversations : targetType='.$userConvsMap[$id]['targetType'].',convNo='.$userConvsMap[$id]['convNo'].',targetId='.$id.', error = '.$e->getMessage(
                    )
                );
            }
        }
    }

    protected function getTargetTitle($id, $targetType)
    {
        if ($targetType == 'course') {
            $course = $this->getCourseService()->getCourse($id);
            $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

            return $courseSet['title'].'-'.$course['title'];
        }
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $classroom['title'];
    }

    /**
     * @return ConversationService
     */
    protected function getConversationService()
    {
        return $this->createService('IM:ConversationService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
