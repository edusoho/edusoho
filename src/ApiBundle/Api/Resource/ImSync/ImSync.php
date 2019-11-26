<?php

namespace ApiBundle\Api\Resource\ImSync;

use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use ApiBundle\Api\ApiRequest;
use Biz\IM\ConversationException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\SettingService;
use Biz\IM\Service\ConversationService;
use Biz\Classroom\Service\ClassroomService;

class ImSync extends AbstractResource
{
    //每次请求允许创建的最大会话数量
    const MAX_CREATION_PER_TIME = 20;

    public function add(ApiRequest $request)
    {
        $setting = $this->getSettingservice()->get('app_im', array());
        if (empty($setting['enabled'])) {
            throw ConversationException::NOTFOUND_CONVERSATION();
        }

        $user = $this->getCurrentUser();

        $this->syncClassroomConversations($user);

        $this->syncCourseConversations($user);

        return $this->joinGlobalConversation($user);
    }

    protected function joinGlobalConversation($user)
    {
        $conv = $this->getConversationService()->getConversationByTarget(0, 'global');
        if (!$conv) {
            throw ConversationException::NOTFOUND_CONVERSATION();
        }

        $convMember = $this->getConversationService()->getMemberByConvNoAndUserId($conv['no'], $user['id']);
        if ($convMember) {
            return array('convNo' => $convMember['convNo']);
        }

        try {
            $convMember = $this->getConversationService()->joinConversation($conv['no'], $user['id']);

            return array('convNo' => $convMember['convNo']);
        } catch (\Exception $e) {
            throw ConversationException::JOIN_FAILED();
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
                $this->biz['logger']->debug('MemberSync syncCourseConversationMembers quitConversation : convNo='.$convMember['convNo'].',targetId='.$convMember['targetId']);
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
                $this->biz['logger']->debug('MemberSync syncClassroomConversationMembers quitConversation : convNo='.$convMember['convNo'].',targetId='.$convMember['targetId']);
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
                ++$cnt;
                if ($cnt > self::MAX_CREATION_PER_TIME) {
                    break;
                }
                $this->biz['logger']->debug('MemberSync joinConversations & create : targetType='.$targetType.', targetId='.$id.', userId='.$user['id']);
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
                $this->biz['logger']->debug('MemberSync joinConversations & join : targetType='.$targetType.',convNo='.$targetConvsMap[$id]['no'].',targetId='.$id);
                $this->getConversationService()->joinConversation($targetConvsMap[$id]['no'], $user['id']);
            }
        }
    }

    protected function quitConversations($targetIds, $userConvs, $user)
    {
        $userConvsMap = ArrayToolkit::index($userConvs, 'targetId');
        foreach ($targetIds as $id) {
            try {
                $this->biz['logger']->debug('MemberSync quitConversations : targetType='.$userConvsMap[$id]['targetType'].',convNo='.$userConvsMap[$id]['convNo'].',targetId='.$id);
                $this->getConversationService()->quitConversation($userConvsMap[$id]['convNo'], $user['id']);
            } catch (\Exception $e) {
                $this->biz['logger']->error('MemberSync quitConversations : targetType='.$userConvsMap[$id]['targetType'].',convNo='.$userConvsMap[$id]['convNo'].',targetId='.$id.', error = '.$e->getMessage());
            }
        }
    }

    protected function getTargetTitle($id, $targetType)
    {
        if ('course' == $targetType) {
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
        return $this->service('IM:ConversationService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
