<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ChaosThreads extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $threads    = array();
        $conditions = $request->query->all();

        $cursors = isset($conditions['cursor']) ? explode(',', $conditions['cursor']) : array(0, 0, 0);
        $starts  = isset($conditions['start']) ? explode(',', $conditions['start']) : array(0, 0, 0);

        $limit = $request->query->get('limit', 20);

        // thread表的话题
        $conditions = array(
            'status'        => 'open',
            'updateTime_GE' => isset($cursors[0]) ? $cursors[0] : 0
        );
        $start         = isset($starts[0]) ? $starts[0] : 0;
        $commonThreads = $this->getThreadService()->searchThreads($conditions, array(array('updateTime', 'ASC')), $start, $limit);
        $commonThreads = $this->normalizeCommonThreads($commonThreads);

        $commonNext = $this->nextCursorPaging($conditions['updateTime_GE'], $start, $limit, $commonThreads);
        $threads    = array_merge($threads, $this->filterCommonThreads($commonThreads));

        // course_thread表的话题
        $conditions = array(
            'private'        => 0,
            'updatedTime_GE' => isset($cursors[1]) ? $cursors[1] : 0
        );
        $start         = isset($starts[1]) ? $starts[1] : 0;
        $courseThreads = $this->getCourseThreadService()->searchThreads($conditions, array(array('updatedTime', 'ASC')), $start, $limit);
        $courseNext    = $this->nextCursorPaging($conditions['updatedTime_GE'], $start, $limit, $courseThreads);
        $threads       = array_merge($threads, $this->filterCourseThreads($courseThreads));

        // group_thread表的话题
        $conditions = array(
            'updatedTime_GE' => isset($cursors[2]) ? $cursors[2] : 0
        );
        $start        = isset($starts[2]) ? $starts[2] : 0;
        $groupThreads = $this->getGroupThreadService()->searchThreads($conditions, array(array('updatedTime', 'ASC')), $start, $limit);
        $groupNext    = $this->nextCursorPaging($conditions['updatedTime_GE'], $start, $limit, $groupThreads);
        $threads      = array_merge($threads, $this->filterGroupThreads($groupThreads));

        $next = array(
            'cursor' => implode(',', array($commonNext['cursor'], $courseNext['cursor'], $groupNext['cursor'])),
            'start'  => implode(',', array($commonNext['start'], $courseNext['start'], $groupNext['start'])),
            'limit'  => $limit,
            'eof'    => ($commonNext['eof'] && $courseNext['eof'] && $groupNext['eof']) ? true : false
        );

        return $this->wrap($this->filter($threads), $next);
    }

    public function post(Application $app, Request $request)
    {
        $fields = $request->request->all();

        if (!ArrayToolkit::requireds($fields, array('threadType'))) {
            return array('message' => '缺少必填字段threadType');
        }

        switch ($fields['threadType']) {
            case 'common':

                if (!ArrayToolkit::requireds($fields, array('title', 'content', 'targetId', 'type', 'targetType'))) {
                    return array('message' => '缺少必填字段');
                }

                $fields = ArrayToolkit::parts($fields, array('title', 'content', 'targetId', 'type', 'targetType'));
                $thread = $this->getThreadService()->createThread($fields);
                break;

            case 'course':

                if (!ArrayToolkit::requireds($fields, array('title', 'content', 'courseId', 'type'))) {
                    return array('message' => '缺少必填字段');
                }

                $fields = ArrayToolkit::parts($fields, array('title', 'content', 'courseId', 'type', 'lessonId'));
                $thread = $this->getCourseThreadService()->createThread($fields);
                break;

            case 'group':
                $currentUser = $this->getCurrentUser();

                if (!ArrayToolkit::requireds($fields, array('title', 'content', 'groupId'))) {
                    return array('message' => '缺少必填字段');
                }

                $fields['userId'] = $currentUser['id'];
                $fields           = ArrayToolkit::parts($fields, array('title', 'content', 'groupId', 'userId'));
                $thread           = $this->getGroupThreadService()->addThread($fields);
                break;

            default:
                return array('message' => 'threadType类型不正确');
                break;
        }

        return $this->callFilter('Thread', $thread);
    }

    public function filter(&$res)
    {
        return $res;
    }

    protected function filterGroupThreads($groupThreads)
    {
        $threads = array();

        foreach ($groupThreads as $thread) {
            $threads[] = array(
                'id'          => $thread['id'],
                'title'       => $thread['title'],
                'content'     => $thread['content'],
                'postNum'     => $thread['postNum'],
                'hitNum'      => $thread['hitNum'],
                'userId'      => $thread['userId'],
                'targetId'    => $thread['groupId'],
                'targetType'  => 'group',
                'createdTime' => date('c', $thread['createdTime']),
                'updatedTime' => date('c', $thread['updatedTime'])
            );
        }

        return $threads;
    }

    protected function filterCourseThreads($courseThreads)
    {
        $threads = array();

        foreach ($courseThreads as $thread) {
            $threads[] = array(
                'id'          => $thread['id'],
                'title'       => $thread['title'],
                'content'     => $thread['content'],
                'postNum'     => $thread['postNum'],
                'hitNum'      => $thread['hitNum'],
                'userId'      => $thread['userId'],
                'targetId'    => $thread['courseId'],
                'targetType'  => 'course',
                'createdTime' => date('c', $thread['createdTime']),
                'updatedTime' => date('c', $thread['updatedTime'])
            );
        }

        return $threads;
    }

    protected function normalizeCommonThreads(&$commonThreads)
    {
        foreach ($commonThreads as &$thread) {
            $thread['updatedTime'] = $thread['updateTime'];
            unset($thread['updateTime']);
        }

        return $commonThreads;
    }

    protected function filterCommonThreads($commonThreads)
    {
        $threads = array();

        foreach ($commonThreads as $thread) {
            $threads[] = array(
                'id'          => $thread['id'],
                'title'       => $thread['title'],
                'content'     => $thread['content'],
                'postNum'     => $thread['postNum'],
                'hitNum'      => $thread['hitNum'],
                'userId'      => $thread['userId'],
                'targetId'    => $thread['targetId'],
                'targetType'  => $thread['targetType'],
                'createdTime' => date('c', $thread['createdTime']),
                'updatedTime' => date('c', $thread['updatedTime'])
            );
        }

        return $threads;
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    protected function getCourseThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getGroupThreadService()
    {
        return $this->getServiceKernel()->createService('Group.ThreadService');
    }
}
