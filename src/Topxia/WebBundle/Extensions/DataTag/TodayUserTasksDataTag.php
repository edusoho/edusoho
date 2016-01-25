<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

/**
 * @todo  
 */
class TodayUserTasksDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取用户今日任务
     *
     * 可传入的参数：
     *   userId   必需 用户ID
     *   taskType 必需 任务类型, 例如学习计划任务
     *   batchId  可选 批次ID, 例如学习计划ID
     *   count    必需 课程数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 任务列表
     */
    public function getData(array $arguments)
    {   
        $this->checkArguments($arguments);

        $conditions = array(
            'userId' => $arguments['userId'],
            'taskType' => $arguments['taskType'],
            'status' => 'active'
        );
        if (isset($arguments['batchId']) && !empty($arguments['batchId'])) {
            $conditions['batchId'] = $arguments['batchId'];
        }

        $tasks = $this->getTaskService()->searchTasks($conditions,array('taskStartTime','ASC'), 0, $arguments['count']);

        $time = strtotime(date('Y-m-d').' 23:59:59');
        if ($tasks) {
            foreach($tasks as $key => $task) {
                if ($task['taskEndTime'] < $time) {
                    $tasks[$key]['timeStatus'] = 'behind';
                }
                else if ($task['taskEndTime'] > $time) {
                    $tasks[$key]['timeStatus'] = 'ahead';
                }
                else {
                    $tasks[$key]['timeStatus'] = 'normal';
                }
            }
        }

        return $tasks;
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task.TaskService');
    }

    protected function checkArguments(array $arguments)
    {
        if (empty($arguments['userId'])) {
            throw new \InvalidArgumentException("userId参数缺失");
        }

        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException("count参数缺失");
        }
        if ($arguments['count'] > 100) {
            throw new \InvalidArgumentException("count参数超出最大取值范围");
        }
    }
}
