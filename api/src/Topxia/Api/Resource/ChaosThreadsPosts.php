<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ChaosThreadsPosts extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $fields = $request->request->all();

        if (!ArrayToolkit::requireds($fields, array('threadType'))) {
            return array('message' => '缺少必填字段threadType');
        }

        switch ($fields['threadType']) {
            case 'common':
                if (!ArrayToolkit::requireds($fields, array('parentId'))) {
                    return array('message' => '缺少必填字段');
                }

                $fields = ArrayToolkit::parts($fields, array('threadId', 'parentId', 'content'));

                $post = $this->getThreadService()->createPost($fields);
                break;

            case 'course':
                if (!ArrayToolkit::requireds($fields, array('courseId', 'content', 'threadId'))) {
                    return array('message' => '缺少必填字段');
                }

                if (!$this->getCourseService()->canTakeCourse($fields['courseId'])) {
                    return array('message' => '没有发布话题权限');
                }

                $fields = ArrayToolkit::parts($fields, array('threadId', 'content', 'courseId'));
                $post = $this->getCourseThreadService()->createPost($fields);
                break;

            case 'group':
                $currentUser = $this->getCurrentUser();

                if (!ArrayToolkit::requireds($fields, array('threadId', 'content', 'groupId'))) {
                    return array('message' => '缺少必填字段');
                }

                $fields['userId'] = $currentUser['id'];
                $fields['postId'] = isset($fields['postId']) ? $fields['postId'] : 0;
                $fields = ArrayToolkit::parts($fields, array('content', 'groupId', 'userId', 'threadId', 'postId'));
                $postContent = array(
                    'content' => $fields['content'],
                    'fromUserId' => 0,
                );

                $post = $this->getGroupThreadService()->postThread($postContent, $fields['groupId'], $fields['userId'], $fields['threadId'], $fields['postId']);
                break;

            default:
                return array('message' => 'threadType类型不正确');
                break;
        }

        return $this->filter($post);
    }

    public function getThreadPosts(Application $app, Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);
        if ($this->getCurrentUser()->isTeacher()) {
            $members = $this->getCourseMemberService()->findTeacherMembersByUserId($currentUser['id']);
            $courseIds = ArrayToolkit::column($members, 'courseId');
            if (empty($courseIds)) {
                $questionIds = array();
            } else {
                $threads = $this->getCourseThreadService()->searchThreads(array('courseIds' => $courseIds, 'type' => 'question'), array(), 0, PHP_INT_MAX);
                $questionIds = ArrayToolkit::column($threads, 'id');
            }
        }
        $threadPosts = $this->getCourseThreadService()->getMyLatestReplyPerThread(0, PHP_INT_MAX);
        $threadPosts = ArrayToolkit::index($threadPosts, 'threadId');
        $threadIds = ArrayToolkit::column($threadPosts, 'threadId');
        $threadIds = isset($questionIds) ? array_merge($questionIds, $threadIds) : $threadIds;

        if (empty($threadIds)) {
            return array();
        }
        $courseThreads = $this->getCourseThreadService()->searchThreads(array('ids' => $threadIds), 'postedNotStick', $start, $limit);

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
                $thread['threadId'] = $thread['id'];
                $thread['lessonId'] = $thread['taskId'];
                $course = $courses[$thread['courseId']];
                $thread['course'] = $this->filterCourse($course);
                $thread['threadContent'] = convertAbsoluteUrl($thread['content']);
                $thread['content'] = isset($threadPosts[$thread['id']]) ? convertAbsoluteUrl($threadPosts[$thread['id']]['content']) : '';
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

    public function filter($res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);

        return $res;
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread:ThreadService');
    }

    protected function getCourseThreadService()
    {
        return $this->getServiceKernel()->createService('Course:ThreadService');
    }

    protected function getGroupThreadService()
    {
        return $this->getServiceKernel()->createService('Group:ThreadService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->getServiceKernel()->createService('Course:CourseSetService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }
}
