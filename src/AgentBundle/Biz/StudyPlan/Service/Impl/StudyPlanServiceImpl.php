<?php

namespace AgentBundle\Biz\StudyPlan\Service\Impl;

use AgentBundle\Biz\StudyPlan\Dao\AiStudyConfigDao;
use AgentBundle\Biz\StudyPlan\Dao\StudyPlanDao;
use AgentBundle\Biz\StudyPlan\Dao\StudyPlanDetail;
use AgentBundle\Biz\StudyPlan\Factory\CalculationStrategyFactory;
use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use AgentBundle\Biz\StudyPlan\StudyPlanException;
use AgentBundle\Client\AgentClient;
use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;

class StudyPlanServiceImpl extends BaseService implements StudyPlanService
{
    public function createConfig($aiStudyConfig)
    {
        $client = new AgentClient($this->biz);
        $course = $this->getCourseService()->getCourse($aiStudyConfig['courseId']);
        $result = $client->createDataset([
            'course' => $course,
            'aiStudyConfig' => $aiStudyConfig
        ]);
        $aiStudyConfig['isActive'] = 1;
        // 零时把错误数据变成1，需要删掉做异常处理
        $aiStudyConfig['databaseId'] = $result['id'] ?? 1;
        $this->getAiStudyConfigDao()->create($aiStudyConfig);
    }

    public function updateConfig($aiStudyConfig)
    {
        $this->getAiStudyConfigDao()->update($aiStudyConfig['id'], $aiStudyConfig);
    }

    public function getGenerateConfig($data)
    {
        $generateConfig = $this->getAiStudyConfigDao()->getAiStudyConfigByCourseId($data['courseId']);
        if (empty($generateConfig) || $generateConfig['isActive'] == 0) {
            return ['status' => 'AI_DISABLED'];
        }

        return [
            'status' => 'ok',
            'data' => $generateConfig,
        ];
    }

    public function generate($params)
    {
        $activities = $this->getActivityLearnTime($params['courseId']);
        // 获取学习全部任务时间
        $totalStudyTime = array_sum(array_column($activities, 'learnTime'));
        // 计算全部可学习天数
        $params['startTime'] = strtotime($params['startDate']);
        $params['endTime'] = strtotime($params['endDate']);
        list($learnTotalDay, $dates) = $this->getLearnTotalDay($params['startTime'], $params['endTime'], $params['weekDays']);
        // 计算每天学多长时间
        $learnTimePerDay = ceil($totalStudyTime / $learnTotalDay);
        // 每天学习时长 / 每天每个任务学习时长 = 每天学习几个任务
        $waitLearnTasks = $this->getTaskService()->searchTasks(['courseId' => $params['courseId'], 'activityIds' => array_column($activities, 'id')], [], 0, PHP_INT_MAX);
        // 构建 activityId => learnTime 的映射
        $activityMap = [];
        foreach ($activities as $activity) {
            $activityMap[$activity['id']] = $activity['learnTime'];
        }

        // 填充 learnTime 到任务中
        foreach ($waitLearnTasks as &$task) {
            $activityId = $task['activityId'];
            $task['time'] = $activityMap[$activityId] ?? 0; // 处理未找到的情况
        }
        unset($task); // 重要：清除引用
        $this->getStudyPlanDao()->create([
            'userId' => $this->getCurrentUser()->getId(),
            'courseId' => $params['courseId'],
            'startDate' => $params['startTime'],
            'endDate' => $params['endTime'],
            'totalDays' => $learnTotalDay,
            'weekDays' => $params['weekDays'],
            'dailyAvgTime' => $learnTimePerDay,
        ]);
        $studyPlan = $this->generateStudyPlan($dates, $learnTimePerDay, $waitLearnTasks);

        return $studyPlan;
    }

    private function convertToMarkdown()
    {

    }

    protected function getActivityLearnTime($courseId)
    {
        $taskResults = $this->getTaskResultService()->findUserFinishedTaskResultsByCourseId($courseId);
        $conditions = ['fromCourseId' => $courseId];
        if (!empty($taskResults)) {
            $conditions['excludeIds'] = array_column($taskResults, 'activityId');
        }
        $activities = $this->getActivityService()->search(
            $conditions,
            [],
            0,
            PHP_INT_MAX['id']
        );
        if (empty($activities)) {
            $this->createNewException(StudyPlanException::LEARN_TASK_NOT_BE_EMPTY());
        }
        $activities = $this->getActivityService()->findActivities(array_column($activities, 'id'), true);
        foreach ($activities as &$activity) {
            $activity['learnTime'] = CalculationStrategyFactory::create($activity)->calculateTime($activity);
        }

        return $activities;
    }

    /**
     * 计算全部学习天数
     *
     * @param $startTime
     * @param $endTime
     * @param $weekDays
     *
     * @return int
     */
    public function getLearnTotalDay($startTime, $endTime, $weekDays)
    {
        date_default_timezone_set('Asia/Shanghai');

        function getFirstOccurrence($startTime, $weekday) {
            $currentWeekday = date('N', $startTime);
            if ($currentWeekday == $weekday) {
                return $startTime;
            } else {
                return strtotime('next ' . ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$weekday - 1], $startTime);
            }
        }

        $count = 0;
        $dates = array();
        $weekarray = array("日", "一", "二", "三", "四", "五", "六"); // 定义中文星期数组

        foreach ($weekDays as $weekday) {
            $current = getFirstOccurrence($startTime, $weekday);
            while ($current <= $endTime) {
                ++$count;
                $dateStr = date('Y-m-d', $current);
                // 获取中文星期几
                $chineseWeekday = "星期" . $weekarray[date('w', $current)]; // date('w')返回0-6对应日-六
                $dates[] = array(
                    'date' => $dateStr,
                    'weekday' => $chineseWeekday
                );
                $current = strtotime('+1 week', $current);
            }
        }

        return [$count, $dates];
    }
//    public function getLearnTotalDay($startTime, $endTime, $weekDays)
//    {
//        // 设置时区（与服务器时区一致）
//        date_default_timezone_set('Asia/Shanghai');
//        function getFirstOccurrence($startTime, $weekday)
//        {
//            $currentWeekday = date('N', $startTime);
//            if ($currentWeekday == $weekday) {
//                return $startTime;
//            } else {
//                return strtotime('next '.['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$weekday - 1], $startTime);
//            }
//        }
//        $count = 0;
//        foreach ($weekDays as $weekday) {
//            $current = getFirstOccurrence($startTime, $weekday);
//            while ($current <= $endTime) {
//                ++$count;
//                $current = strtotime('+1 week', $current);
//            }
//        }
//
//        return $count;
//    }

    /**
     *
     * @param int $dailyTime
     * @param array $tasks
     * @return array
     */
    protected function generateStudyPlan(array $dates, int $dailyTime, array $tasks): array
    {
        // 检查任务时间是否合法
        foreach ($tasks as $task) {
            if ($task['time'] > $dailyTime) {
//                throw new InvalidArgumentException("任务 {$task['id']} 时间超过每日上限");
            }
        }

        // 按任务时间降序排序（关键优化）
        usort($tasks, function ($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        $days = [];
        foreach ($tasks as $task) {
            $allocated = false;
            $dataIndex = 0;
            // 尝试将任务放入已有的天数
            foreach ($days as &$day) {
                if ($day['remaining'] >= $task['time']) {
                    $day['tasks'][] = [
                        'id' => $task['id'],
                        'time' => $task['time'],
                        'title' => $task['title'],
                        'date' => $dates[$dataIndex]['date'],
                        'weekday' => $dates[$dataIndex]['weekday']
                    ];
                    $day['remaining'] -= $task['time'];
                    $allocated = true;
                    break;
                }
            }

            // 无法放入则创建新天数
            if (!$allocated) {
                $dataIndex++;
                $days[] = [
                    'tasks' => [
                        [
                            'id' => $task['id'],
                            'time' => $task['time'],
                            'title' => $task['title'],
                            'date' => $dates[$dataIndex]['date'],
                            'weekday' => $dates[$dataIndex]['weekday']
                        ],
                    ],
                    'remaining' => $dailyTime - $task['time'],
                ];
            }
        }

        return $days;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return AiStudyConfigDao
     */
    protected function getAiStudyConfigDao()
    {
        return $this->createDao('AgentBundle:StudyPlan:AiStudyConfigDao');
    }

    /**
     * @return StudyPlanDao
     */
    protected function getStudyPlanDao()
    {
        return $this->createDao('AgentBundle:StudyPlan:StudyPlanDao');
    }

    /**
     * @return StudyPlanDetail
     */
    protected function getStudyPlanDetail()
    {
        return $this->createDao('AgentBundle:StudyPlan:StudyPlanDetail');
    }
}
