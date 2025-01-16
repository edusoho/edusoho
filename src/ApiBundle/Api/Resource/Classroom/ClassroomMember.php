<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\LearningDataAnalysisService;
use Biz\Exception\UnableJoinException;
use Biz\MemberOperation\Service\MemberOperationService;
use Biz\Visualization\Service\CoursePlanLearnDataDailyStatisticsService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClassroomMember extends AbstractResource
{
    public function add(ApiRequest $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if (!$classroom) {
            throw ClassroomException::NOTFOUND_CLASSROOM();
        }

        $member = $this->getClassroomService()->getClassroomMember($classroomId, $this->getCurrentUser()->getId());
        if (!$member || $member['role'] == ['auditor']) {
            $member = $this->tryJoin($classroom);
        }

        if ($member) {
            $this->getOCUtil()->single($member, ['userId']);
            $member['isOldUser'] = true;

            return $member;
        }

        return null;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if (!$classroom) {
            throw ClassroomException::NOTFOUND_CLASSROOM();
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = ArrayToolkit::parts($request->query->all(), [
            'startTimeGreaterThan',
            'startTimeLessThan',
            'joinedChannel',
            'deadlineAfter',
            'deadlineBefore',
            'userKeyword',
        ]);
        $conditions['classroomId'] = $classroomId;
        if (isset($conditions['userKeyword']) && '' != $conditions['userKeyword']) {
            $conditions['userIds'] = $this->getUserService()->getUserIdsByKeyword($conditions['userKeyword']);
            unset($conditions['userKeyword']);
        }

        if ($request->query->get('role', '')) {
            $conditions['role'] = $request->query->get('role');
        }

        $total = $this->getClassroomService()->searchMemberCount($conditions);
        $members = $this->getClassroomService()->searchMembers($conditions, ['createdTime' => 'DESC'], $offset, $limit);
        if ('student' == $conditions['role'] ?? '') {
            $this->appendLearningProgress($members, $classroomId);
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));
        $roles = $this->getCurrentUser()->getRoles();
        foreach ($members as &$member) {
            $member['user'] = empty($users[$member['userId']]) ? null : $users[$member['userId']];
            // 是学员就不显示
            $member['needHideNickname'] = is_array($roles) && count($roles) > 1 ? false : true;;
            $member['joinedChannelText'] = $this->convertJoinedChannel($member);
            $member['remark'] = in_array($member['remark'], ['site.join_by_free', 'site.join_by_purchase']) ? '' : $member['remark'];
        }

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    private function tryJoin($classroom)
    {
        try {
            $this->getClassroomService()->tryFreeJoin($classroom['id']);
        } catch (UnableJoinException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $this->getCurrentUser()->getId());
        if (!empty($member)) {
            $this->getLogService()->info('classroom', 'join_classroom', "加入班级《{$classroom['title']}》", ['classroomId' => $classroom['id'], 'title' => $classroom['title']]);
        }

        return $member;
    }

    private function appendLearningProgress(&$classroomMembers, $classroomId)
    {
        $courses = $this->getClassroomService()->findByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($courses, 'courseId');
        foreach ($classroomMembers as &$classroomMember) {
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress(
                $classroomMember['classroomId'],
                $classroomMember['userId']
            );
            $classroomMember['learningProgressPercent'] = $progress['percent'];
            $conditions = [
                'userId' => $classroomMember['userId'],
                'courseIds' => $courseIds,
            ];
            $learningTime = $this->getCoursePlanLearnDataDailyStatisticsService()->sumLearnedTimeByConditions($conditions);
            $classroomMember['learningTime'] = round($learningTime / 60);
        }
    }

    private function convertJoinedChannel($member)
    {
        if ('import_join' === $member['joinedChannel']) {
            $records = $this->getMemberOperationService()->searchRecords(['target_type' => 'classroom', 'target_id' => $member['classroomId'], 'user_id' => $member['userId'], 'operate_type' => 'join'], ['id' => 'DESC'], 0, 1);
            if (!empty($records)) {
                $operator = $this->getUserService()->getUser($records[0]['operator_id']);

                return "{$operator['nickname']}添加";
            }
        }

        return ['free_join' => '免费加入', 'buy_join' => '购买加入', 'vip_join' => '会员加入'][$member['joinedChannel']] ?? '';
    }

    /**
     * @return \Biz\System\Service\Impl\LogServiceImpl
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return LearningDataAnalysisService
     */
    protected function getLearningDataAnalysisService()
    {
        return $this->service('Classroom:LearningDataAnalysisService');
    }

    /**
     * @return CoursePlanLearnDataDailyStatisticsService
     */
    private function getCoursePlanLearnDataDailyStatisticsService()
    {
        return $this->service('Visualization:CoursePlanLearnDataDailyStatisticsService');
    }

    /**
     * @return MemberOperationService
     */
    private function getMemberOperationService()
    {
        return $this->service('MemberOperation:MemberOperationService');
    }
}
