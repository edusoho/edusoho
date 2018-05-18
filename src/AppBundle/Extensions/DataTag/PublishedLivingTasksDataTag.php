<?php

namespace AppBundle\Extensions\DataTag;

class PublishedLivingTasksDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取正在直播的已发布任务
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
        $livingTasks = $this->getTaskService()->findPublishedLivingTasksByCourseSetId($arguments['courseSetId']);

        return $livingTasks;
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
