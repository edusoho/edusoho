<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class CourseThread extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $threadId)
    {
        $thread = $this->getCourseThreadService()->getThreadByThreadId($threadId);
        if ($thread['mediaId']) {
            $thread['mediaUri'] = 'XXX';
        }

        return $thread;
    }

    public function search(ApiRequest $request, $courseId)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = array(
            'courseId' => $courseId,
        );

        $total = $this->getCourseThreadService()->countThreads($conditions);
        $threads = $this->getCourseThreadService()->searchThreads(
            $conditions,
            array(),
            $offset,
            $limit
        );

        $this->getOCUtil()->multiple($threads, array('userId'));

        return $this->makePagingObject(array_values($threads), $total, $offset, $limit);
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
