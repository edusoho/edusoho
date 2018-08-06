<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Biz\Course\Util\CourseTitleUtils;

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
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $total = $this->getCourseThreadService()->getMyReplyThreadCount();
        $start = -1 == $start ? rand(0, $total - 1) : $start;

        $posts = $this->getCourseThreadService()->getMyLatestReplyPerThread($start, $limit);
        if (empty($posts)) {
            return array();
        }
        $courseIds = ArrayToolkit::column($posts, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        foreach ($posts as $key => &$post) {
            $thread = $this->getCourseThreadService()->getThread(0, $post['threadId']);

            $course = $courses[$post['courseId']];
            $courseSet = $courseSets[$course['courseSetId']];

            $smallPicture = empty($courseSet['cover']['small']) ? '' : $courseSet['cover']['small'];
            $middlePicture = empty($courseSet['cover']['middle']) ? '' : $courseSet['cover']['middle'];
            $largePicture = empty($courseSet['cover']['large']) ? '' : $courseSet['cover']['large'];
            $course['smallPicture'] = $this->getFileUrl($smallPicture, 'course.png');
            $course['middlePicture'] = $this->getFileUrl($middlePicture, 'course.png');
            $course['largePicture'] = $this->getFileUrl($largePicture, 'course.png');
            $course = CourseTitleUtils::formatTitle($course, $courseSet['title']);

            $post['type'] = $thread['type'];
            $post['title'] = $thread['title'];
            $post['content'] = convertAbsoluteUrl($post['content']);
            $post['course'] = $this->filterCourse($course);
        }

        return array_values($posts);
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
