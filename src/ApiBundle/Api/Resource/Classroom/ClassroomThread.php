<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\ClassroomException;
use Biz\Group\Service\ThreadService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Group\ThreadException;

class ClassroomThread extends AbstractResource
{
    public function search(ApiRequest $request, $classroomId)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $sort = $request->query->get('sort', 'posted');

        $conditions = array(
            'targetType' => 'classroom',
            'targetId' => $classroomId,
            'type' => $request->query->get('type', ''),
        );

        $total = $this->getThreadService()->searchThreadCount($conditions);
        $threads = $this->getThreadService()->searchThreads($conditions, $sort, $offset, $limit);
        $this->getOCUtil()->multiple($threads, array('userId'));

        return $this->makePagingObject($threads, $total, $offset, $limit);
    }

    public function get(ApiRequest $request, $classroomId, $threadId)
    {
        if (!$this->getClassroomService()->canTakeClassroom($classroomId)) {
            throw ClassroomException::FORBIDDEN_TAKE_CLASSROOM();
        }

        $thread = $this->getThreadService()->getThread($threadId);
        if (empty($thread)) {
            throw ThreadException::NOTFOUND_THREAD();
        }

        $this->getOCUtil()->single($thread, array('userId'));
        $this->getOCUtil()->single($thread, array('targetId'), 'classroom');

        return $thread;
    }

    public function add(ApiRequest $request, $classroomId)
    {
        if (!$this->getClassroomService()->canTakeClassroom($classroomId)) {
            throw ClassroomException::FORBIDDEN_TAKE_CLASSROOM();
        }

        $thread = array(
            'title' => $request->request->get('title'),
            'content' => $request->request->get('content'),
            'targetId' => $classroomId,
            'type' => $request->request->get('type'),
            'targetType' => 'classroom',
        );

        $thread = $this->getThreadService()->createThread($thread);
        $this->getOCUtil()->single($thread, array('userId'));

        return $thread;
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->service('Thread:ThreadService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
