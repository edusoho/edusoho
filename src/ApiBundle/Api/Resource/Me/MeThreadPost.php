<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use ApiBundle\Api\Annotation\ResponseFilter;

class MeThreadPost extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Course\CourseThreadFilter", mode="public")
     */
    public function search(ApiRequest $request)
    {
        $currentUser = $this->getCurrentUser();

        $threadPosts = $this->getCourseThreadService()->getMyLatestReplyPerThread(0, PHP_INT_MAX);
        $threadPosts = ArrayToolkit::index($threadPosts, 'threadId');

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
        $threadIds = ArrayToolkit::column($threadPosts, 'threadId');
        $threadIds = isset($questionIds) ? array_merge($questionIds, $threadIds) : $threadIds;

        if (empty($threadIds)) {
            return (object) array();
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $total = $this->getCourseThreadService()->countThreads(array('ids' => $threadIds));

        $courseThreads = $this->getCourseThreadService()->searchThreads(array('ids' => $threadIds), 'postedNotStick', $offset, $limit);

        if (empty($courseThreads)) {
            return (object) array();
        }

        $posts = $this->getCourseThreadService()->searchThreadPosts(array('threadIds' => ArrayToolkit::column($courseThreads, 'id'), 'isRead' => 0, 'exceptedUserId' => $currentUser['id']), array(), 0, PHP_INT_MAX);
        $posts = ArrayToolkit::group($posts, 'threadId');

        foreach ($courseThreads as &$thread) {
            $thread['lastPost'] = isset($threadPosts[$thread['id']]) ? $threadPosts[$thread['id']] : array();
            $thread['notReadPostNum'] = isset($posts[$thread['id']]) ? count($posts[$thread['id']]) : 0;
        }

        $this->getOCUtil()->multiple($courseThreads, array('courseId'), 'course');

        return $this->makePagingObject($courseThreads, $total, $offset, $limit);
    }

    protected function getCourseThreadService()
    {
        return $this->service('Course:ThreadService');
    }

    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
