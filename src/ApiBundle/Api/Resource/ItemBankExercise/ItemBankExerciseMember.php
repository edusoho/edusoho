<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\ItemBankExercise\Service\MemberOperationRecordService;
use Biz\User\Service\UserService;

class ItemBankExerciseMember extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $exerciseId)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = ArrayToolkit::parts($request->query->all(), [
            'startTimeGreaterThan',
            'startTimeLessThan',
            'joinedChannel',
            'deadlineAfter',
            'deadlineBefore',
            'userKeyword',
        ]);
        $conditions['role'] = 'student';
        $conditions['exerciseId'] = $exerciseId;
        $conditions['locked'] = 0;
        if (isset($conditions['joinedChannel']) && in_array($conditions['joinedChannel'], ['course_join', 'classroom_join'])) {
            $bindExercises = $this->getItemBankExerciseService()->findExerciseBindByExerciseId($exerciseId);
            $bindExercises = array_filter($bindExercises, function ($bindExercise) use ($conditions) {
                if ('course_join' == $conditions['joinedChannel']) {
                    return 'course' == $bindExercise['bindType'];
                } elseif ('classroom_join' == $conditions['joinedChannel']) {
                    return 'classroom' == $bindExercise['bindType'];
                }
            });
            $bindExerciseIds = array_column($bindExercises, 'id');
            $autoJoinRecords = $this->getItemBankExerciseService()->findExerciseAutoJoinRecordByItemBankExerciseIdAndItemBankExerciseBindIds($exerciseId, $bindExerciseIds);
            $conditions['userIds'] = array_column($autoJoinRecords, 'userId');
            $conditions['joinedChannel'] = 'bind_join';
        }
        if (isset($conditions['userKeyword']) && '' != $conditions['userKeyword']) {
            $userIdsByKeyword = $this->getUserService()->getUserIdsByKeyword($conditions['userKeyword']);
            if (!empty($conditions['userIds'])) {
                $conditions['userIds'] = array_intersect($userIdsByKeyword, $conditions['userIds']);
            } else {
                $conditions['userIds'] = $userIdsByKeyword;
            }
            unset($conditions['userKeyword']);
        }
        if (isset($conditions['userIds']) && empty($conditions['userIds'])) {
            $conditions['userIds'] = [-1];
        }
        $members = $this->getItemBankExerciseMemberService()->search(
            $conditions,
            ['createdTime' => 'DESC'],
            $offset,
            $limit
        );
        $roles = $this->getCurrentUser()->getRoles();
        foreach ($members as &$member) {
            $member['user'] = empty($users[$member['userId']]) ? null : $users[$member['userId']];
            // 是学员就不显示
            $member['needHideNickname'] = is_array($roles) && count($roles) > 1 ? false : true;
            $member['joinedChannelText'] = $this->convertJoinedChannel($member);
            $member['remark'] = in_array($member['remark'], ['site.join_by_free', 'site.join_by_purchase']) ? '' : $member['remark'];
        }

        $total = $this->getItemBankExerciseMemberService()->count($conditions);

        $this->getOCUtil()->multiple($members, ['userId']);

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    public function add(ApiRequest $request, $exerciseId)
    {
        $member = $this->getItemBankExerciseMemberService()->getExerciseStudent($exerciseId, $this->getCurrentUser()->getId());

        if (!$member) {
            $member = $this->getItemBankExerciseService()->freeJoinExercise($exerciseId);
        }

        $this->getOCUtil()->single($member, ['userId']);

        return $member;
    }

    private function convertJoinedChannel($member)
    {
        if ('import_join' === $member['joinedChannel']) {
            $records = $this->getMemberOperationRecordService()->search(['exerciseId' => $member['exerciseId'], 'memberId' => $member['id'], 'operateType' => 'join'], ['id' => 'DESC'], 0, 1);
            if (!empty($records)) {
                $operator = $this->getUserService()->getUser($records[0]['operatorId']);

                return "{$operator['nickname']}添加";
            }
        }
        if ('bind_join' === $member['joinedChannel']) {
            $autoRecords = $this->getItemBankExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseId([$member['userId']], $member['exerciseId']);
            $exerciseBinds = $this->getItemBankExerciseService()->findBindExerciseByIds(array_column($autoRecords, 'itemBankExerciseBindId'));
            $exerciseBindGroups = ArrayToolkit::group($exerciseBinds, 'bindType');

            $joinedChannels = [];

            // 处理课程绑定
            if (!empty($exerciseBindGroups['course'])) {
                $courseIds = array_column($exerciseBindGroups['course'], 'bindId');
                $courses = $this->getCourseService()->findCoursesByIds($courseIds);
                foreach ($courses as $course) {
                    $joinedChannels[] = '《'.$course['courseSetTitle'].'》课程加入';
                }
            }

            // 处理班级绑定
            if (!empty($exerciseBindGroups['classroom'])) {
                $classroomIds = array_column($exerciseBindGroups['classroom'], 'bindId');
                $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);
                foreach ($classrooms as $classroom) {
                    $joinedChannels[] = '《'.$classroom['title'].'》班级加入';
                }
            }

            // 拼接所有加入渠道，并去掉最后的 "、"
            return rtrim(implode('、', $joinedChannels), '、');
        }

        return ['free_join' => '免费加入', 'buy_join' => '购买加入'][$member['joinedChannel']] ?? '';
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseMemberService
     */
    protected function getItemBankExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MemberOperationRecordService
     */
    protected function getMemberOperationRecordService()
    {
        return $this->service('ItemBankExercise:MemberOperationRecordService');
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
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
