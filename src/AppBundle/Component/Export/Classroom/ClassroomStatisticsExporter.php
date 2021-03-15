<?php

namespace AppBundle\Component\Export\Classroom;

use AppBundle\Component\Export\Exporter;
use Biz\Classroom\Service\ClassroomService;

class ClassroomStatisticsExporter extends Exporter
{
    //定义导出标题
    public function getTitles()
    {
        return [
            'admin.classroom_manage.statistics.name_th',
            'admin.classroom_manage.statistics.course_number_th',
            'admin.classroom_manage.statistics.task_number_th',
            'admin.classroom_manage.statistics.student_number_th',
            'admin.classroom_manage.statistics.finish_number_th',
            'admin.classroom_manage.statistics.income_th',
            'admin.classroom_manage.statistics.create_time_th',
            'admin.classroom_manage.statistics.creator_th',
        ];
    }

    //获得导出正文内容
    public function getContent($start, $limit)
    {
        $classrooms = $this->getClassroomService()->searchClassroomsWithStatistics(
            $this->conditions,
            ['createdTime' => 'desc'],
            $start,
            $limit
        );

        $creators = $this->getUserService()->findUsersByIds(array_values(array_unique(array_column($classrooms, 'creator'))));
        $creators = array_column($creators, null, 'id');

        $content = [];
        foreach ($classrooms as $classroom) {
            $nickname = empty($creators[$classroom['creator']]) ? '' : $creators[$classroom['creator']]['nickname'];
            $content[] = [
                $classroom['title'],
                $classroom['courseNum'],
                empty($classroom['electiveTaskNum']) ? $classroom['compulsoryTaskNum'] : $classroom['compulsoryTaskNum'].'('.$classroom['electiveTaskNum'].')',
                $classroom['studentNum'],
                $classroom['finishedMemberCount'],
                $classroom['income'],
                (string) date('Y-m-d H:i:s', $classroom['createdTime']),
                is_numeric($nickname) ? $nickname."\t" : $nickname,
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
        return $this->getClassroomService()->countClassrooms($this->conditions);
    }

    //构建查询条件
    public function buildCondition($conditions)
    {
        $conditions = $this->fillOrgCode($conditions);

        return $conditions;
    }

    protected function fillOrgCode($conditions)
    {
        if ($this->getSettingService()->node('magic.enable_org')) {
            if (!isset($conditions['orgCode'])) {
                $conditions['likeOrgCode'] = $this->getUser()->getSelectOrgCode();
            } else {
                $conditions['likeOrgCode'] = $conditions['orgCode'];
                unset($conditions['orgCode']);
            }
        } else {
            if (isset($conditions['orgCode'])) {
                unset($conditions['orgCode']);
            }
        }

        return $conditions;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }
}
