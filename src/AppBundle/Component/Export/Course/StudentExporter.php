<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class StudentExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();
        if ($user->isAdmin()) {
            return true;
        }

        $courseSetting = $this->getSettingService()->get('course', []);
        if (!empty($courseSetting['teacher_export_student'])) {
            $this->getCourseService()->tryManageCourse($this->parameter['courseId'], $this->parameter['courseSetId']);

            return true;
        }

        return false;
    }

    public function getCount()
    {
        return $this->getCourseMemberService()->countMembers($this->conditions);
    }

    public function getTitles()
    {
        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $userFieldsTitle = empty($userFields) ? [] : ArrayToolkit::column($userFields, 'title');
        $fields = [
            'user.fields.username_label',
            'user.fields.email_label',
            'task.learn_data_detail.createdTime',
            'course.plan_task.study_rate',
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
        $course = $this->getCourseService()->getCourse($this->parameter['courseId']);
        $translator = $this->container->get('translator');
        $gender = [
            'female' => $translator->trans('user.fields.gender.female'),
            'male' => $translator->trans('user.fields.gender.male'),
            'secret' => $translator->trans('user.fields.gender.secret'),
        ];

        $courseMembers = $this->getCourseMemberService()->searchMembers(
            $this->conditions,
            ['createdTime' => 'DESC'],
            $start,
            $limit
        );

        $studentUserIds = ArrayToolkit::column($courseMembers, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);

        foreach ($courseMembers as $key => $member) {
            $progress = $this->getLearningDataAnalysisService()->makeProgress($member['learnedCompulsoryTaskNum'], $course['compulsoryTaskNum']);
            $courseMembers[$key]['learningProgressPercent'] = $progress['percent'];
        }

        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $fields = ArrayToolkit::column($fields, 'fieldName');

        $datas = [];
        foreach ($courseMembers as $courseMember) {
            $member = [];
            $userId = $courseMember['userId'];
            $profile = $profiles[$userId];
            $user = $users[$userId];

            $member[] = $user['nickname']."\t";
            $member[] = $user['email'];
            $member[] = date('Y-n-d H:i:s', $courseMember['createdTime']);
            $member[] = $courseMember['learningProgressPercent'].'%';
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
        $parameter['courseId'] = $conditions['courseId'];
        $parameter['courseSetId'] = $conditions['courseSetId'];

        return $parameter;
    }

    public function buildCondition($conditions)
    {
        return [
            'courseId' => $conditions['courseId'],
            'role' => 'student',
        ];
    }

    /**
     * @return LearningDataAnalysisService
     */
    protected function getLearningDataAnalysisService()
    {
        return $this->getBiz()->service('Course:LearningDataAnalysisService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->getBiz()->service('User:UserFieldService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
