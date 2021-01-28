<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Task\Service\TaskService;

class FreeTasksDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取免费课程课程列表.
     *
     * 可传入的参数：
     *   categoryId 可选 分类ID
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['count'])) {
            $arguments['count'] = 4;
        }

        return $this->getTaskService()->searchTasks(array('isFree' => 1), array('createdTime' => 'DESC'), 0, $arguments['count']);
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->getServiceKernel()->getBiz()->service('Task:TaskService');
    }
}
