<?php

namespace AppBundle\Extensions\DataTag;

class ThreadDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个话题.
     *
     * 可传入的参数：
     *   threadId 必需 课程话题ID
     *
     * @param array $arguments 参数
     *
     * @return array 话题
     */
    public function getData(array $arguments)
    {
        $this->checkThreadId($arguments);

        $thread = $this->getThreadService()->getThread($arguments['threadId']);
        if (empty($thread)) {
            return null;
        }

        return $thread;
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->getBiz()->service('Thread:ThreadService');
    }
}
