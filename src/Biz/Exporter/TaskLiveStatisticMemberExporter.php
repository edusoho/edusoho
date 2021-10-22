<?php

namespace Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\SimpleValidator;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\LiveStatistics\Service\Impl\LiveCloudStatisticsServiceImpl;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use PHPExcel_Exception;

class TaskLiveStatisticMemberExporter extends BaseSheetAddStyleExporter
{
    protected $task = [];

    public function getExportFileName()
    {
        $time = date('Y_m_d_H_i', time());

        return  "直播统计_{$time}.xls";
    }

    public function getSortedHeadingRow()
    {
        return [
                '用户名' => 'nickname',
                '手机号' => 'mobile',
                '邮箱' => 'email',
                '进入直播间时间' => 'firstEnterTime',
                '观看时长（分）' => 'watchDuration',
                '签到数' => 'checkinNum',
                '聊天数' => 'chatNum',
                '答题数' => 'answerNum',
             ];
    }

    public function buildExportSheetData($params)
    {
        $sheetIndex = 0;
        try {
            $this->PHPExcel->createSheet($sheetIndex);
            $sheet = $this->PHPExcel->setActiveSheetIndex($sheetIndex);
            $this->setDefaultRowHeight();
            $this->PHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(110);
            //换行
            $this->PHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
            $sheet->setCellValue('A1', $this->getHeadValue($params));
            $data = $this->buildData($params);
            $row = count($data) + 2;
            $this->setBorders('A1:H2');
            $this->setSize(['A1'], 14);
            $this->setBold(['A1:H2']);
            $this->setVerticalCenter(['A1']);
            $this->setHorizontalCenter(['A1', 'A2', 'B2:H'.$row]);
            $this->mergeCells('A1:H1');
            $sheet->setTitle('直播统计');
            $this->setWidth(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']);

            $this->setSheetCellValue($sheet, $data);
        } catch (PHPExcel_Exception $e) {
            throw $e;
        }
    }

    protected function getHeadValue($params)
    {
        $this->task = $this->getTaskService()->getTask($params['taskId']);
        $course = $this->getCourseService()->getCourse($this->task['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($this->task['fromCourseSetId']);
        $result = $this->getLiveStatisticsService()->getLiveData($this->task);
        $title = (empty($course['title']) ? $courseSet['title'] : $course['title']).'-'.$this->task['title']."\r\n\r\n";
        $startTime = date('Y-m-d H:i', $result['startTime']);
        $endTime = date('Y-m-d H:i', $result['endTime']);
        $detail1 = "主讲人：{$result['teacher']} \t\t     直播时间： {$startTime}至{$endTime} \t\t     实际直播时长： {$result['length']} \r\n\r\n";
        $detail2 = "同时在线人数：{$result['maxOnlineNumber']} \t\t    观看人数：{$result['memberNumber']} \t\t    用户聊天数：{$result['chatNumber']} \t\t    人均观看时长：{$result['avgWatchTime']}\r\n";

        return "\r\n".$title.$detail1.$detail2;
    }

    protected function buildData($params)
    {
        $activity = $this->getActivityService()->getActivity($this->task['activityId'], true);
        $params['liveId'] = $activity['ext']['liveId'];
        $conditions = ArrayToolkit::parts($this->buildUserConditions($params), ['courseId', 'liveId', 'userIds']);
        $members = $this->getLiveStatisticsService()->searchCourseMemberLiveData($conditions, 0, PHP_INT_MAX, ['firstEnterTime', 'watchDuration', 'checkinNum', 'chatNum', 'answerNum', 'userId']);
        $cloudStatisticData = $activity['ext']['cloudStatisticData'];
        $userIds = ArrayToolkit::column($members, 'userId');
        $users = $this->getUserService()->searchUsers(['userIds' => empty($userIds) ? [-1] : $userIds], [], 0, count($userIds), ['id', 'nickname', 'verifiedMobile', 'email', 'emailVerified']);
        $users = ArrayToolkit::index($users, 'id');
        foreach ($members as &$member) {
            $member['firstEnterTime'] = empty($member['firstEnterTime']) ? '--' : date('Y-m-d H:i', $member['firstEnterTime']);
            $member['nickname'] = empty($users[$member['userId']]) ? '--' : $users[$member['userId']]['nickname'];
            $member['email'] = empty($users[$member['userId']]) || empty($users[$member['userId']]['emailVerified']) ? '--' : $users[$member['userId']]['email'];
            $member['checkinNum'] = empty($cloudStatisticData['checkinNum']) ? '--' : $member['checkinNum'].'/'.$cloudStatisticData['checkinNum'];
            $member['mobile'] = empty($users[$member['userId']]) || empty($users[$member['userId']]['verifiedMobile']) ? '--' : $users[$member['userId']]['verifiedMobile'];
            $member['watchDuration'] = round($member['watchDuration'] / 60, 1);
        }

        return $members;
    }

    protected function buildUserConditions($params)
    {
        if (!empty($params['nameOrMobile'])) {
            $mobile = SimpleValidator::mobile($params['nameOrMobile']);
            if ($mobile) {
                $user = $this->getUserService()->getUserByVerifiedMobile($params['nameOrMobile']);
                $users = empty($user) ? [] : [$user];
            } else {
                $users = $this->getUserService()->searchUsers(
                    ['nickname' => $params['nameOrMobile']],
                    [],
                    0,
                    PHP_INT_MAX,
                    ['id']
                );
            }
            $userIds = ArrayToolkit::column($users, 'id');
            $params['userIds'] = empty($userIds) ? [-1] : $userIds;
        }
        unset($params['nameOrMobile']);

        return $params;
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
     * @return LiveCloudStatisticsServiceImpl
     */
    protected function getLiveStatisticsService()
    {
        return $this->createService('LiveStatistics:LiveCloudStatisticsService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
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
}
