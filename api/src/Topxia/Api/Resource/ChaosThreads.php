<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ChaosThreads extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $threads = array();
        $conditions = $request->query->all();

        $cursors = isset($conditions['cursor']) ? explode(',', $conditions['cursor']) : array(0, 0, 0);
        $starts = isset($conditions['start']) ? explode(',', $conditions['start']) : array(0, 0, 0);

        $limit = $request->query->get('limit', 20);

        // thread表的话题
        $conditions = array(
            'status' => 'open',
            'updateTime_GE' => isset($cursors[0]) ? $cursors[0] : 0,
        );
        $start = isset($starts[0]) ? $starts[0] : 0;
        $commonThreads = $this->getThreadService()->searchThreads($conditions, array('updateTime' => 'ASC'), $start, $limit);
        $commonThreads = $this->normalizeCommonThreads($commonThreads);

        $commonNext = $this->nextCursorPaging($conditions['updateTime_GE'], $start, $limit, $commonThreads);
        $threads = array_merge($threads, $this->filterCommonThreads($commonThreads));

        // course_thread表的话题
        $conditions = array(
            'private' => 0,
            'updatedTime_GE' => isset($cursors[1]) ? $cursors[1] : 0,
        );
        $start = isset($starts[1]) ? $starts[1] : 0;
        $courseThreads = $this->getCourseThreadService()->searchThreads($conditions, array('updatedTime' => 'ASC'), $start, $limit);
        $courseNext = $this->nextCursorPaging($conditions['updatedTime_GE'], $start, $limit, $courseThreads);
        $threads = array_merge($threads, $this->filterCourseThreads($courseThreads));

        // group_thread表的话题
        $conditions = array(
            'updatedTime_GE' => isset($cursors[2]) ? $cursors[2] : 0,
        );
        $start = isset($starts[2]) ? $starts[2] : 0;
        $groupThreads = $this->getGroupThreadService()->searchThreads($conditions, array('updatedTime' => 'ASC'), $start, $limit);
        $groupNext = $this->nextCursorPaging($conditions['updatedTime_GE'], $start, $limit, $groupThreads);
        $threads = array_merge($threads, $this->filterGroupThreads($groupThreads));

        $next = array(
            'cursor' => implode(',', array($commonNext['cursor'], $courseNext['cursor'], $groupNext['cursor'])),
            'start' => implode(',', array($commonNext['start'], $courseNext['start'], $groupNext['start'])),
            'limit' => $limit,
            'eof' => ($commonNext['eof'] && $courseNext['eof'] && $groupNext['eof']) ? true : false,
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

                if (!$this->getCourseService()->canTakeCourse($fields['courseId'])) {
                    return array('message' => '没有发布话题权限');
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
                $fields = ArrayToolkit::parts($fields, array('title', 'content', 'groupId', 'userId'));
                $thread = $this->getGroupThreadService()->addThread($fields);
                break;

            default:
                return array('message' => 'threadType类型不正确');
                break;
        }

        return $this->callFilter('Thread', $thread);
    }

    public function filter($res)
    {
        return $res;
    }

    protected function filterGroupThreads($groupThreads)
    {
        $threads = array();

        foreach ($groupThreads as $thread) {
            $threads[] = array(
                'id' => $thread['id'],
                'title' => $thread['title'],
                'content' => $thread['content'],
                'postNum' => $thread['postNum'],
                'hitNum' => $thread['hitNum'],
                'userId' => $thread['userId'],
                'targetId' => $thread['groupId'],
                'targetType' => 'group',
                'createdTime' => date('c', $thread['createdTime']),
                'updatedTime' => date('c', $thread['updatedTime']),
            );
        }

        return $threads;
    }

    protected function filterCourseThreads($courseThreads)
    {
        $threads = array();

        foreach ($courseThreads as $thread) {
            $threads[] = array(
                'id' => $thread['id'],
                'title' => $thread['title'],
                'content' => $thread['content'],
                'postNum' => $thread['postNum'],
                'hitNum' => $thread['hitNum'],
                'userId' => $thread['userId'],
                'targetId' => $thread['courseId'],
                'targetType' => 'course',
                'createdTime' => date('c', $thread['createdTime']),
                'updatedTime' => date('c', $thread['updatedTime']),
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
                'id' => $thread['id'],
                'title' => $thread['title'],
                'content' => $thread['content'],
                'postNum' => $thread['postNum'],
                'hitNum' => $thread['hitNum'],
                'userId' => $thread['userId'],
                'targetId' => $thread['targetId'],
                'targetType' => $thread['targetType'],
                'createdTime' => date('c', $thread['createdTime']),
                'updatedTime' => date('c', $thread['updatedTime']),
            );
        }

        return $threads;
    }

    public function getThreads(Application $app, Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);
        $conditions = array(
            'userId' => $currentUser['id'],
        );

        $userCourses = $this->getCourseMemberService()->searchMembers(array('userId' => $currentUser['id']), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);

        if (!$userCourses) {
            return array();
        }

        $total = $this->getCourseThreadService()->countThreads($conditions);

        $start = $start == -1 ? rand(0, $total - 1) : $start;

        $courseThreads = $this->getCourseThreadService()->searchThreads($conditions, 'postedNotStick', $start, $limit);

        if (empty($courseThreads)) {
            return array();
        }

        $courseIds = ArrayToolkit::column($courseThreads, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courseSets = $this->getCourseSetService()->findCourseSetsByCourseIds($courseIds);

        foreach ($courses as $key => $course) {
            $courses[$key]['courseSet'] = $courseSets[$course['courseSetId']];
        }

        $courses = $this->multicallFilter('Course', $courses);
        $posts = $this->getCourseThreadService()->searchThreadPosts(array('threadIds' => ArrayToolkit::column($courseThreads, 'id'), 'isRead' => 0, 'exceptedUserId' => $currentUser['id']), array(), 0, PHP_INT_MAX);
        $posts = ArrayToolkit::group($posts, 'threadId');

        foreach ($courseThreads as $key => $thread) {
            if (isset($courses[$thread['courseId']])) {
                $thread = ArrayToolkit::rename($thread, array('private' => 'isPrivate'));
                $thread['lessonId'] = $thread['taskId'];
                $course = $courses[$thread['courseId']];
                $thread['course'] = $this->filterCourse($course);
                $thread['content'] = convertAbsoluteUrl($thread['content']);
                $thread['notReadPostNum'] = isset($posts[$thread['id']]) ? count($posts[$thread['id']]) : 0;
                $courseThreads[$key] = $thread;
            } else {
                unset($courseThreads[$key]);
            }
        }

        return $courseThreads;
    }

    protected function filterCourse($course)
    {
        $keys = array(
            'id',
            'type',
            'title',
            'userId',
            'smallPicture',
            'middlePicture',
            'largePicture',
            'createdTime',
        );

        return ArrayToolkit::parts($course, $keys);
    }

    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    protected function getCourseThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    protected function getGroupThreadService()
    {
        return $this->createService('Group:ThreadService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
