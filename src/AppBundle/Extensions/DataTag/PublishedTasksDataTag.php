<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Task\Service\TaskService;

class PublishedTasksDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取课程的所有任务
     *
     * 可传入的参数：
     *   courseSetId 必需 课程ID
     *
     * @param array $arguments 参数
     *
     * @return array 任务
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['courseSetId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('courseSetId参数缺失'));
        }

        $conditions = array(
            'fromCourseSetId' => $arguments['courseSetId'],
            'status' => 'published',
        );

        if (!empty($arguments['type'])) {
            $conditions['type'] = $arguments['type'];
        }

        if (empty($arguments['count'])) {
            $count = $this->getTaskService()->countTasks($conditions);
        } else {
            $count = intval($arguments['count']);
        }

        return $this->getTaskService()->searchTasks(
            $conditions,
            array('startTime' => 'ASC'),
            0,
            $count
        );
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
