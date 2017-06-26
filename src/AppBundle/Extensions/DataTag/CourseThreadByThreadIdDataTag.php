<?php

namespace AppBundle\Extensions\DataTag;

class CourseThreadByThreadIdDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个课程话题.
     *
     * 可传入的参数：
     *   threadId 必需 课程话题ID
     *
     * @param array $arguments 参数
     *
     * @return array 课程话题
     */
    public function getData(array $arguments)
    {
        $this->checkThreadId($arguments);

        $thread = $this->getThreadService()->getThread($arguments['courseId'], $arguments['threadId']);
        if (empty($thread)) {
            return null;
        }

        return $thread;
    }
}
