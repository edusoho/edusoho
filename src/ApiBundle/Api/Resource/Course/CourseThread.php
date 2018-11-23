<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class CourseThread extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $threadId)
    {
        $thread = $this->getCourseThreadService()->getThreadByThreadId($threadId);
        $thread['user'] = $this->getUserService()->getUser($thread['userId']);

        return $thread;
    }

    public function search(ApiRequest $request, $courseId)
    {
        $type = $request->query->get('type', 'question');
        $title = $request->query->get('title');
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = array(
            'courseId' => $courseId,
            'type' => $type,
            'title' => $title,
        );

        $this->createSearchKeyword($title, $type);

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

    public function add(ApiRequest $request, $courseId)
    {
        return array(1111);
    }

    protected function createSearchKeyword($title, $type)
    {
        $keyword = $this->getSearchKeywordService()->getSearchKeywordByNameAndType($title, $type);
        if ($keyword) {
            $this->getSearchKeywordService()->addSearchKeywordTimes($keyword['id']);
            $result = $this->getSearchKeywordService()->getSearchKeyword($keyword['id']);
        } else {
            $result = $this->getSearchKeywordService()->createSearchKeyword(array('name' => $title));
        }

        return $result;
    }

    /**
     * @return \Biz\SearchKeyword\Service\Impl\SearchKeywordServiceImpl
     */
    protected function getSearchKeywordService()
    {
        return $this->service('SearchKeyword:SearchKeywordService');
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

    /**
     * @return \Biz\User\Service\Impl\UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
