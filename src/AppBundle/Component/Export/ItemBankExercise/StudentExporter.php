<?php

namespace AppBundle\Component\Export\ItemBankExercise;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\ItemBankExercise\Service\MemberOperationRecordService;
use Biz\User\Service\UserFieldService;

class StudentExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();
        if ($user->isAdmin()) {
            return true;
        }

        $this->getExerciseService()->tryManageExercise($this->parameter['exerciseId']);

        return false;
    }

    public function getCount()
    {
        return $this->getExerciseMemberService()->count($this->conditions);
    }

    public function getTitles()
    {
        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $userFieldsTitle = empty($userFields) ? [] : ArrayToolkit::column($userFields, 'title');
        $fields = [
            'user.fields.username_label',
            'user.fields.email_label',
            'user.fields.phone_label',
            'join.channel',
            'join.time',
            'course.marketing_setup.rule.expiry_date',
            'task.learn_data_detail.createdTime',
            'exercise.answers.done_num',
            'exercise.answers.completion_rate',
            'exercise.answers.mastery_rate',
            'user.fields.truename_label',
            'user.fields.gender_label',
            'user.fileds.qq',
            'user.fileds.wechat',
            'user.fields.mobile_label',
            'user.fields.company_label',
            'user.fields.career_label',
            'user.fields.title_label',
            'student.profile.weibo',
        ];

        return array_merge($fields, $userFieldsTitle);
    }

    public function getContent($start, $limit)
    {
        $translator = $this->container->get('translator');
        $gender = [
            'female' => $translator->trans('user.fields.gender.female'),
            'male' => $translator->trans('user.fields.gender.male'),
            'secret' => $translator->trans('user.fields.gender.secret'),
        ];

        $exerciseMembers = $this->getExerciseMemberService()->search(
            $this->conditions,
            ['createdTime' => 'DESC'],
            $start,
            $limit
        );

        $studentUserIds = ArrayToolkit::column($exerciseMembers, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);

        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $fields = ArrayToolkit::column($fields, 'fieldName');

        $datas = [];
        foreach ($exerciseMembers as $exerciseMember) {
            $member = [];
            $userId = $exerciseMember['userId'];
            $profile = $profiles[$userId];
            $user = $users[$userId];

            $member[] = $user['nickname']."\t";
            $member[] = $user['email'];
            $member[] = empty($user['mobile']) ? (empty($userProfile['mobile']) ? '-' : $userProfile['mobile']) : $user['mobile'];
            $member[] = $this->convertJoinedChannel($exerciseMember);
            $member[] = date('Y-n-d H:i:s', $exerciseMember['createdTime']);
            $member[] = 0 == $exerciseMember['deadline'] ? '长期有效' : date('Y-n-d H:i:s', $exerciseMember['deadline']);
            $member[] = date('Y-n-d H:i:s', $exerciseMember['createdTime']);
            $member[] = $exerciseMember['doneQuestionNum'];
            $member[] = $exerciseMember['completionRate'].'%';
            $member[] = $exerciseMember['masteryRate'].'%';
            $member[] = $profile['truename'] ? $profile['truename'] : '-';
            $member[] = $gender[$profile['gender']];
            $member[] = $profile['qq'] ? $profile['qq'] : '-';
            $member[] = $profile['weixin'] ? $profile['weixin'] : '-';
            $member[] = $profile['mobile'] ? $profile['mobile'] : '-';
            $member[] = $profile['company'] ? $profile['company'] : '-';
            $member[] = $profile['job'] ? $profile['job'] : '-';
            $member[] = $user['title'] ? $user['title'] : '-';
            $member[] = $profile['weibo'] ? $profile['weibo'] : '-';

            foreach ($fields as $value) {
                $member[] = $profile[$value] ? str_replace([PHP_EOL, '"'], '', $profile[$value]) : '-';
            }

            $datas[] = $member;
        }

        return $datas;
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

    public function buildParameter($conditions)
    {
        $parameter = parent::buildParameter($conditions);
        $parameter['exerciseId'] = $conditions['exerciseId'];

        return $parameter;
    }

    public function buildCondition($conditions)
    {
        if ($this->hasJoinedChannel($conditions)) {
            $conditions = $this->processJoinedChannel($conditions);
        }
        if ($this->hasUserKeyword($conditions)) {
            $conditions = $this->processUserKeyword($conditions);
        }
        $conditions['userIds'] = $this->ensureUserIds($conditions);
        return $this->buildFinalConditions($conditions);
    }

    private function hasJoinedChannel($conditions)
    {
        return isset($conditions['joinedChannel']) && '' != $conditions['joinedChannel'];
    }

    private function processJoinedChannel($conditions)
    {
        $exerciseId = $conditions['exerciseId'];
        $bindExercises = $this->getItemBankExerciseService()->findExerciseBindByExerciseId($exerciseId);

        $bindExercises = array_filter($bindExercises, function ($bindExercise) use ($conditions) {
            if ('course_join' == $conditions['joinedChannel']) {
                return 'course' == $bindExercise['bindType'];
            } elseif ('classroom_join' == $conditions['joinedChannel']) {
                return 'classroom' == $bindExercise['bindType'];
            }

            return false;
        });

        $bindExerciseIds = array_column($bindExercises, 'id');
        $autoJoinRecords = $this->getItemBankExerciseService()->findExerciseAutoJoinRecordByItemBankExerciseIdAndItemBankExerciseBindIds($exerciseId, $bindExerciseIds);
        $conditions['userIds'] = array_column($autoJoinRecords, 'userId');
        if (in_array($conditions['joinedChannel'], ['course_join', 'classroom_join')) {
            $conditions['joinedChannel'] = 'bind_join';
        }

        return $conditions;
    }

    private function hasUserKeyword($conditions)
    {
        return isset($conditions['userKeyword']) && '' != $conditions['userKeyword'];
    }

    private function processUserKeyword($conditions)
    {
        $userIdsByKeyword = $this->getUserService()->getUserIdsByKeyword($conditions['userKeyword']);
        if (!empty($conditions['userIds'])) {
            $conditions['userIds'] = array_intersect($userIdsByKeyword, $conditions['userIds']);
        } else {
            $conditions['userIds'] = $userIdsByKeyword;
        }
        unset($conditions['userKeyword']);

        return $conditions;
    }

    private function ensureUserIds($conditions)
    {
        if (isset($conditions['userIds']) && empty($conditions['userIds'])) {
            return [-1];
        }

        return $conditions['userIds'] ?? [];
    }

    private function buildFinalConditions($conditions)
    {
        $defaultConditions = [
            'exerciseId' => $conditions['exerciseId'],
            'role' => 'student',
            'startTimeGreaterThan' => $conditions['startTimeGreaterThan'] ?? '',
            'startTimeLessThan' => $conditions['startTimeLessThan'] ?? '',
            'joinedChannel' => $conditions['joinedChannel'] ?? '',
            'deadlineAfter' => $conditions['deadlineAfter'] ?? '',
            'deadlineBefore' => $conditions['deadlineBefore'] ?? '',
            'userIds' => $conditions['userIds'] ?? [],
        ];

        return $defaultConditions;
    }

    public function postExport()
    {
        $this->getLogService()->warning('item_bank_exercise', 'export_students', '导出学员数据');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->getBiz()->service('User:UserFieldService');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return MemberOperationRecordService
     */
    protected function getMemberOperationRecordService()
    {
        return $this->getBiz()->service('ItemBankExercise:MemberOperationRecordService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }
}
