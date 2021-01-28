<?php

namespace AppBundle\Extensions\DataTag;

class CourseThreadByPostIdDataTag extends CourseBaseDataTag implements DataTag
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
        $this->checkPostId($arguments);

        $post = $this->getThreadService()->getPost($arguments['courseId'], $arguments['postId']);
        $thread = $this->getThreadService()->getThread($courseId = null, $post['threadId']);
        if (empty($thread)) {
            return null;
        }

        return $thread;
    }

    protected function checkPostId(array $arguments)
    {
        if (empty($arguments['postId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('postId参数缺失'));
        }
    }
}
