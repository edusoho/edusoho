<?php

namespace AppBundle\Extensions\DataTag;

class CourseThreadDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个课程话题.
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     *   threadId 必需 课程话题ID
     *
     * @param array $arguments 参数
     *
     * @return array 课程话题
     */
    public function getData(array $arguments)
    {
        $this->checkCourseId($arguments);
        $this->checkThreadId($arguments);

        $thread = $this->getThreadService()->getThread($arguments['courseId'], $arguments['threadId']);
        if (empty($thread)) {
            return null;
        }

        $thread['course'] = $this->getCourseService()->getCourse($arguments['courseId']);

        return $thread;
    }
}
