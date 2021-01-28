<?php

namespace AppBundle\Extensions\DataTag;

class CourseRelatedDataDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个课程相关数据，如帖子数、资料数等.
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     *
     * @param array $arguments 参数
     *
     * @return array 课程数据
     */
    public function getData(array $arguments)
    {
        $this->checkCourseId($arguments);
        $courseData = array();

        $threadConditions = array(
            'courseId' => $arguments['courseId'],
            'types' => array('discussion', 'question'),
        );
        $courseData['threadNum'] = $this->getThreadService()->countThreads($threadConditions);

        $materialConditions = array(
            'courseId' => $arguments['courseId'],
            'excludeLessonId' => 0,
            'source' => 'coursematerial',
        );
        $courseData['materialNum'] = $this->getCourseMaterialService()->countMaterials($materialConditions);

        return $courseData;
    }

    protected function getCourseMaterialService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:MaterialService');
    }
}
