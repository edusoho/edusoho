<?php

namespace Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Live\Service\LiveStatisticsService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use PHPExcel_Exception;

class TaskRolCallExporter extends BaseSheetAddStyleExporter
{
    public function getExportFileName()
    {
        $time = date('Y_m_d_H_i', time());

        return "直播点名统计_{$time}.xls";
    }

    public function getSortedHeadingRow()
    {
        return [
                '用户名' => 'nickname',
                '手机号' => 'mobile',
                '邮箱' => 'email',
                '是否点名' => 'checkin',
             ];
    }

    public function buildExportSheetData($params)
    {
        $sheetIndex = 0;
        try {
            $this->PHPExcel->createSheet($sheetIndex);
            $sheet = $this->PHPExcel->setActiveSheetIndex($sheetIndex);
            $this->setDefaultRowHeight();
            $data = $this->buildData($params);
            $this->setBold(['A1:E1']);
            $this->setVerticalCenter(['A1']);
            $sheet->setTitle('直播点名统计');
            $this->setWidth(['A', 'B', 'C', 'D', 'E']);
            $this->setSheetCellValue($sheet, $data);
        } catch (PHPExcel_Exception $e) {
            throw $e;
        }
    }

    protected function buildData($params)
    {
        $task = $this->getTaskService()->getTask($params['taskId']);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $statistics = $this->getLiveStatisticsService()->getCheckinStatisticsByLiveId($activity['ext']['liveId']);
        if (!empty($params['status']) && !empty($statistics['data']['detail'])) {
            $groupedStatistics = ArrayToolkit::group($statistics['data']['detail'], 'checkin');
            $groupedStatistics = [
                empty($groupedStatistics[0]) ? [] : $groupedStatistics[0],
                empty($groupedStatistics[1]) ? [] : $groupedStatistics[1],
            ];
            $statistics['data']['detail'] = 'checked' == $params['status'] ? $groupedStatistics[1] : $groupedStatistics[0];
        }
        $statistics = empty($statistics['data']['detail']) ? [] : $statistics['data']['detail'];
        if (empty($statistics)) {
            return [];
        }
        $userIds = ArrayToolkit::column($statistics, 'userId');
        $users = $this->getUserService()->searchUsers(['userIds' => empty($userIds) ? [-1] : $userIds], [], 0, count($userIds), ['id', 'nickname', 'verifiedMobile', 'email', 'emailVerified']);
        $users = ArrayToolkit::index($users, 'id');
        $data = [];
        foreach ($statistics as $statistic) {
            $data[] = [
                'nickname' => empty($users[$statistic['userId']]) ? '--' : $users[$statistic['userId']]['nickname'],
                'mobile' => empty($users[$statistic['userId']]) || empty($users[$statistic['userId']]['verifiedMobile']) ? '--' : $users[$statistic['userId']]['verifiedMobile'],
                'email' => empty($users[$statistic['userId']]) || empty($users[$statistic['userId']]['emailVerified']) ? '--' : $users[$statistic['userId']]['email'],
                'checkin' => $statistic['checkin'] ? $this->trans('course.live_statistics.checkin_status.checked') : $this->trans('course.live_statistics.checkin_status.not_checked'),
            ];
        }

        return $data;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return LiveStatisticsService
     */
    protected function getLiveStatisticsService()
    {
        return $this->createService('Live:LiveStatisticsService');
    }
}
