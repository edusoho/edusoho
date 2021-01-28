<?php

namespace AppBundle\Extensions\DataTag;

class CourseThreadPostDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个课程话题回复.
     *
     * 可传入的参数：
     *   postId   必需 回复ID
     *
     * @param array $arguments 参数
     *
     * @return array 课程话题回复
     */
    public function getData(array $arguments)
    {
        $this->checkPostId($arguments);

        $post = $this->getThreadService()->getPost($arguments['courseId'], $arguments['postId']);
        if (empty($post)) {
            return null;
        }

        $post['thread'] = $this->getThreadService()->getThread($post['courseId'], $post['threadId']);

        return $post;
    }

    protected function checkPostId(array $arguments)
    {
        if (empty($arguments['postId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('postId参数缺失'));
        }
    }
}
