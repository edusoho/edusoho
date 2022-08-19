<?php

namespace AppBundle\Component\Export\ItemBankExercise;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\ItemBankExercise\Service\ChapterExerciseRecordService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
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
            $member[] = date('Y-n-d H:i:s', $exerciseMember['createdTime']);
            $member[] = $exerciseMember['doneQuestionNum'];
            $member[] = $exerciseMember['completionRate'] . '%';
            $member[] = $exerciseMember['masteryRate'] . '%';
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

    public function buildParameter($conditions)
    {
        $parameter = parent::buildParameter($conditions);
        $parameter['exerciseId'] = $conditions['exerciseId'];

        return $parameter;
    }

    public function buildCondition($conditions)
    {
        return [
            'exerciseId' => $conditions['exerciseId'],
            'role' => 'student',
        ];
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
     * @return ChapterExerciseRecordService
     */
    protected function getChapterExerciseRecordService()
    {
        return $this->getBiz()->service('ItemBankExercise:ChapterExerciseRecordService');
    }
}
