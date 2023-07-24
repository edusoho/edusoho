<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ContentToolkit;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\Group\ThreadException;
use Biz\Thread\Service\ThreadService;

class ClassroomThread extends AbstractResource
{
    public function search(ApiRequest $request, $classroomId)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $classroomSetting = $this->getSettingService()->get('classroom', []);
        if (isset($classroomSetting['show_thread']) && '0' === $classroomSetting['show_thread']) {
            return $this->makePagingObject([], 0, $offset, $limit);
        }

        $sort = $request->query->get('sort', 'posted');

        $conditions = [
            'targetType' => 'classroom',
            'targetId' => $classroomId,
            'type' => $request->query->get('type', ''),
        ];

        $total = $this->getThreadService()->searchThreadCount($conditions);
        $threads = $this->getThreadService()->searchThreads($conditions, $sort, $offset, $limit);
        foreach ($threads as &$thread) {
            $this->extractImgs($thread);
        }
        $this->getOCUtil()->multiple($threads, ['userId']);

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
        $this->extractImgs($thread);

        $this->getOCUtil()->single($thread, ['userId']);
        $this->getOCUtil()->single($thread, ['targetId'], 'classroom');

        return $thread;
    }

    public function add(ApiRequest $request, $classroomId)
    {
        if (!$this->getClassroomService()->canTakeClassroom($classroomId)) {
            throw ClassroomException::FORBIDDEN_TAKE_CLASSROOM();
        }

        $thread = [
            'title' => $request->request->get('title'),
            'content' => $request->request->get('content'),
            'targetId' => $classroomId,
            'type' => $request->request->get('type'),
            'targetType' => 'classroom',
            'imgs' => $request->request->get('imgs', []),
        ];

        if (isset($thread['imgs'])) {
            $thread['content'] = ContentToolkit::appendImgs($thread['content'], $thread['imgs']);
        }

        $thread = $this->getThreadService()->createThread($thread);
        $this->getOCUtil()->single($thread, ['userId']);

        return $thread;
    }

    protected function extractImgs(&$thread)
    {
        $thread['imgs'] = [];
        if (empty($thread['content'])) {
            return;
        }

        $thread['imgs'] = ContentToolkit::extractImgs($thread['content']);
        $thread['content'] = ContentToolkit::filterImgs($thread['content']);
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
