<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\ClassroomException;
use Biz\Common\CommonException;

class ClassroomThreadPost extends AbstractResource
{
    public function search(ApiRequest $request, $classroomId, $threadId)
    {
        if (!$this->getClassroomService()->canTakeClassroom($classroomId)) {
            throw ClassroomException::FORBIDDEN_TAKE_CLASSROOM();
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $conditions = array(
            'threadId' => $threadId,
            'parentId' => 0,
        );

        $total = $this->getThreadService()->searchPostsCount($conditions);

        $threadPosts = $this->getThreadService()->searchPosts(
            $conditions,
            array('createdTime' => 'DESC'),
            $offset,
            $limit
        );

        $this->getOCUtil()->multiple($threadPosts, array('userId'));

        return $this->makePagingObject($threadPosts, $total, $offset, $limit);
    }

    public function add(ApiRequest $request, $classroomId, $threadId)
    {
        if (!$this->getClassroomService()->canTakeClassroom($classroomId)) {
            throw ClassroomException::FORBIDDEN_TAKE_CLASSROOM();
        }
        $content = $request->request->get('content', '');

        if (empty($content)) {
            throw CommonException::ERROR_PARAMETER();
        }

        $threadPost = array(
            'content' => $content,
            'parentId' => $request->request->get('parentId', 0),
            'threadId' => $threadId,
        );

        $threadPost = $this->getThreadService()->createPost($threadPost);
        $this->getOCUtil()->single($threadPost, array('userId'));

        return $threadPost;
    }

    /**
     * @return \Biz\Course\Classroom\Impl\ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return \Biz\Thread\Service\Impl\ThreadServiceImpl
     */
    protected function getThreadService()
    {
        return $this->service('Thread:ThreadService');
    }
}
