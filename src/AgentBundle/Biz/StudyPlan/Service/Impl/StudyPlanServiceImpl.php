<?php

namespace AgentBundle\Biz\StudyPlan\Service\Impl;

use AgentBundle\Biz\AgentConfig\Dao\AiStudyConfigDao;
use AgentBundle\Biz\StudyPlan\Dao\StudyPlanDao;
use AgentBundle\Biz\StudyPlan\Dao\StudyPlanDetail;
use AgentBundle\Biz\StudyPlan\Factory\CalculationStrategyFactory;
use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use AgentBundle\Biz\StudyPlan\StudyPlanException;
use AgentBundle\Client\AgentClient;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\Common\CommonException;
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
        $aiStudyConfig['datasetId'] = $result['id'] ?? 1;
        $this->getAiStudyConfigDao()->create($aiStudyConfig);
    }

    public function updateConfig($aiStudyConfig)
    {
        $this->getAiStudyConfigDao()->update($aiStudyConfig['id'], $aiStudyConfig);
    }

    public function getGenerateConfig($data)
    {
        $generateConfig = $this->getAiStudyConfigDao()->getByCourseId($data['courseId']);
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
        $learnTotalDay = $this->getLearnTotalDay($params['startTime'], $params['endTime'], $params['weekDays']);
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

        $studyPlans = $this->generateStudyPlan($this->calculateDaysInRange($params['startTime'], $params['endTime'], $params['weekDays'])['days'], $learnTimePerDay, $waitLearnTasks);

        return [
            "status" => 'ok',
            "content" => $this->convertToMarkdown([
                'studyPlans' => $studyPlans,
                'taskCount' => count($waitLearnTasks),
                'total_hours' => $totalStudyTime / 60,
                'study_total' => $learnTotalDay,
                'daily_min' => $learnTimePerDay,
                'study_days' => $this->weekdayConvertChinese($params['weekDays']),
                'course' => $this->getCourseService()->getCourse($params['courseId']),
                'date_range' => date('Y年n月j日', $params['startTime']).'至'.date('Y年n月j日', $params['endTime'])
            ])
        ];
    }

    public function generatePlan($data)
    {
        if (!ArrayToolkit::requireds($data, ['courseId', 'startDate', 'endDate', 'weekDays', 'dailyAvgTime'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $studyPlan = $this->getStudyPlanDao()->getStudyPlanByUserIdAndCourseId($this->getCurrentUser()->getId(), $data['courseId']);
        if (empty($studyPlan)) {
            return $this->getStudyPlanDao()->create([
                'userId' => $this->getCurrentUser()->getId(),
                'courseId' => $data['courseId'],
                'startDate' => $data['startDate'],
                'endDate' => $data['endDate'],
                'weekDays' => $data['weekDays'],
                'totalDays' => 0,
                'dailyAvgTime' => $data['dailyAvgTime'],
            ]);
        }
        $data = ArrayToolkit::parts($data, ['courseId', 'startDate', 'endDate', 'weekDays']);

        return $this->getStudyPlanDao()->update($studyPlan['id'], $data);
    }

    public function isUserStudyPlanGenerated($userId, $courseId)
    {
        $studyPlan = $this->getStudyPlanDao()->getStudyPlanByUserIdAndCourseId($userId, $courseId);

        return !empty($studyPlan);
    }

    private function convertToMarkdown($params)
    {
        // 构建动态表格
        $table = "| 日期 | 学习内容 | 每日学习 |\n";
        $table .= "| ---- | -------- | -------- |\n";

        foreach ($params['studyPlans'] as $studyPlan) {
            foreach ($studyPlan['tasks'] as $task) {
                // 自动处理超链接格式
                $task['link'] = 'course/'.$params['course']['id'].'/task/'.$task['id'].'/show';
                $content = isset($task['link']) ? "[{$task['title']}]({$task['link']})" : $task['title'];
                $table .= sprintf("| %s | %s | %s |\n",
                    $task['date'].'('.$task['weekday'].')',
                    $content,
                    ($task['time']/60).'小时');
            }

        }

        // 组装完整内容
        return <<<MARKDOWN
根据您的需求生成以下学习计划：

1. **学习内容**：{$params['course']['courseSetTitle']}，共{$params['task_count']}个任务，学完需要{$params['total_hours']}小时
2. **学习周期**：{$params['date_range']}  
   每周学习日：{$params['study_days']}，共计{$params['study_total']}个学习日
3. **学习要求**：每次至少学习{$params['daily_min']}小时

以下是详细学习安排：
{$table}
我会在每个学习日提醒您完成目标，请点击表格中的「学习内容」直接访问课程
MARKDOWN;

        return $content;
    }

    private function weekdayConvertChinese($weekdays)
    {
        $weekMap = [
            1 => '周一',
            2 => '周二',
            3 => '周三',
            4 => '周四',
            5 => '周五',
            6 => '周六',
            7 => '周日'
        ];
        $converted = array_map(function($day) use ($weekMap) {
            return $weekMap[$day] ?? '未知'; // 处理无效数字
        }, $weekdays);

        return implode('、', $converted);
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
        // 设置时区（与服务器时区一致）
        date_default_timezone_set('Asia/Shanghai');
        function getFirstOccurrence($startTime, $weekday)
        {
            $currentWeekday = date('N', $startTime);
            if ($currentWeekday == $weekday) {
                return $startTime;
            } else {
                return strtotime('next '.['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$weekday - 1], $startTime);
            }
        }
        $count = 0;
        foreach ($weekDays as $weekday) {
            $current = getFirstOccurrence($startTime, $weekday);
            while ($current <= $endTime) {
                ++$count;
                $current = strtotime('+1 week', $current);
            }
        }

        return $count;
    }

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
        foreach ($tasks as $key => $task) {
            $allocated = false;
            // 尝试将任务放入已有的天数
            foreach ($days as &$day) {
                if ($day['remaining'] >= $task['time']) {
                    $day['tasks'][] = [
                        'id' => $task['id'],
                        'time' => $task['time'],
                        'title' => $task['title'],
                        'date' => $dates[$key]['date'],
                        'weekday' => $dates[$key]['weekday']
                    ];
                    $day['remaining'] -= $task['time'];
                    $allocated = true;
                    break;
                }
            }

            // 无法放入则创建新天数
            if (!$allocated) {
                $days[] = [
                    'tasks' => [
                        [
                            'id' => $task['id'],
                            'time' => $task['time'],
                            'title' => $task['title'],
                            'date' => $dates[$key]['date'],
                            'weekday' => $dates[$key]['weekday']
                        ],
                    ],
                    'remaining' => $dailyTime - $task['time'],
                ];
            }
        }

        return $days;
    }

    function calculateDaysInRange($startTimestamp, $endTimestamp, $weekdays) {
        date_default_timezone_set('Asia/Shanghai');

        $startDate = \DateTime::createFromFormat('U', $startTimestamp);
        $startDate->setTime(0, 0, 0);

        $endDate = \DateTime::createFromFormat('U', $endTimestamp);
        $endDate->setTime(0, 0, 0);

        if ($startDate > $endDate) {
            return ['total' => 0, 'days' => []];
        }

        $weekdayMap = [
            1 => '周一',
            2 => '周二',
            3 => '周三',
            4 => '周四',
            5 => '周五',
            6 => '周六',
            7 => '周日'
        ];

        $matchedDays = [];
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            $dayOfWeek = (int)$currentDate->format('N');

            if (in_array($dayOfWeek, $weekdays)) {
                $matchedDays[] = [
                    'weekday' => $weekdayMap[$dayOfWeek],
                    'date' => $currentDate->format('Y年n月j日') // 修改为中文日期格式
                ];
            }

            $currentDate->modify('+1 day');
        }

        return [
            'total' => count($matchedDays),
            'days' => $matchedDays
        ];
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
