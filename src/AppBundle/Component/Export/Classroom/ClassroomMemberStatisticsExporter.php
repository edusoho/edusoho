<?php

namespace AppBundle\Component\Export\Classroom;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\Classroom\Service\ClassroomService;
use Biz\Visualization\Service\CoursePlanLearnDataDailyStatisticsService;

class ClassroomMemberStatisticsExporter extends Exporter
{
    //定义导出标题
    public function getTitles()
    {
        return [
            'admin.classroom_manage.statistics.member.nickname_th',
            'admin.classroom_manage.statistics.member.phone_number_th',
            'admin.classroom_manage.statistics.member.id_number_th',
            'admin.classroom_manage.statistics.member.create_time_th',
            'admin.classroom_manage.statistics.member.finish_time_th',
            'admin.classroom_manage.statistics.member.learn_time_th',
            'admin.classroom_manage.statistics.member.question_num_th',
            'admin.classroom_manage.statistics.member.note_num_th',
        ];
    }

    //获得导出正文内容
    public function getContent($start, $limit)
    {
        $classroom = $this->getClassroomService()->getClassroom($this->conditions['classroomId']);

        $members = $this->getClassroomService()->findClassroomStudents($classroom['id'], $start, $limit);
        $userIds = ArrayToolkit::column($members, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $classroomCourses = $this->getClassroomService()->findCoursesByClassroomId($classroom['id']);

        $usersLearnedTime = [];
        if (!empty($users) && !empty($classroomCourses)) {
            $usersLearnedTime = $this->getCoursePlanLearnDataDailyStatisticsService()->sumLearnedTimeGroupByUserId([
                'userIds' => array_column($members, 'userId'), 'courseIds' => array_column($classroomCourses, 'id'),
            ]);
            $usersLearnedTime = array_column($usersLearnedTime, null, 'userId');
        }

        $usersProfile = empty($members) ? [] : $this->getUserService()->findUserProfilesByIds($userIds);
        $usersApproval = $this->getUserService()->searchApprovals([
            'userIds' => $userIds,
            'status' => 'approved', ], [], 0, count($userIds));
        $usersApproval = ArrayToolkit::index($usersApproval, 'userId');

        foreach ($users as $key => &$user) {
            $user['mobile'] = isset($usersProfile[$key]['mobile']) ? $usersProfile[$key]['mobile'] : '';
            $user['idcard'] = isset($usersApproval[$key]['idcard']) ? $usersApproval[$key]['idcard'] : '';
        }

        $content = [];
        foreach ($members as $member) {
            $nickname = empty($users[$member['userId']]) ? '--' : $users[$member['userId']]['nickname'];
            $content[] = [
                is_numeric($nickname) ? $nickname."\t" : $nickname,
                empty($users[$member['userId']]['mobile']) ? '--' : $users[$member['userId']]['mobile']."\t",
                empty($users[$member['userId']]['idcard']) ? '--' : $users[$member['userId']]['idcard']."\t",
                date('Y-m-d H:i:s', $member['createdTime']),
                empty($member['finishedTime']) ? '--' : date('Y-m-d H:i:s', $member['finishedTime']),
                empty($usersLearnedTime[$member['userId']]) ? 0.0 : round($usersLearnedTime[$member['userId']]['learnedTime'] / 60, 1),
                $member['questionNum'],
                $member['noteNum'],
            ];
        }

        return $content;
    }

    //下载权限判断
    public function canExport()
    {
        $user = $this->getUser();

        if ($user->hasPermission('admin_v2_classroom_statistics')) {
            return true;
        }

        return false;
    }

    //获得导出总条数
    public function getCount()
    {
        return $this->getClassroomService()->getClassroomStudentCount($this->conditions['classroomId']);
    }

    //构建查询条件
    public function buildCondition($conditions)
    {
        return $conditions;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return CoursePlanLearnDataDailyStatisticsService
     */
    protected function getCoursePlanLearnDataDailyStatisticsService()
    {
        return $this->getBiz()->service('Visualization:CoursePlanLearnDataDailyStatisticsService');
    }
}
