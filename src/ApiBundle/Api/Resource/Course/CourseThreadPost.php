<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class CourseThreadPost extends AbstractResource
{
    public function search(ApiRequest $request, $courseId, $threadId)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = array(
            'threadId' => $threadId,
        );

        $total = $this->getCourseThreadService()->getThreadPostCountByThreadId($threadId);
        $posts = $this->getCourseThreadService()->searchThreadPosts(
            $conditions,
            array(),
            $offset,
            $limit
        );

        $this->getOCUtil()->multiple($threads, array('userId'));

        return $this->makePagingObject(array_values($posts), $total, $offset, $limit);
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return \Biz\Course\Service\Impl\ThreadServiceImpl
     */
    protected function getCourseThreadService()
    {
        return $this->service('Course:ThreadService');
    }
}
