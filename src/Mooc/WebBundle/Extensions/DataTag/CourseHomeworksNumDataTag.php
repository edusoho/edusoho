<?php
namespace Mooc\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

class CourseHomeworksNumDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取最新课程作业数
     *
     * 可传入的参数：
     * courseId 必选 课程ID
     *
     * @param  array $arguments     参数
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $homeworkCount = $this->getHomeWorkService()->searchHomeworkCount(array('courseId' => $arguments['courseId']));

        return $homeworkCount;
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }
}
