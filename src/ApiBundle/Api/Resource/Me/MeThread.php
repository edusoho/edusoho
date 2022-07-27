<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use ApiBundle\Api\Annotation\ResponseFilter;

class MeThread extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Course\CourseThreadFilter", mode="public")
     */
    public function search(ApiRequest $request)
    {
        $currentUser = $this->getCurrentUser();

        $conditions = array(
            'userId' => $currentUser['id'],
        );

        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $courseSetting = $this->getSettingService()->get('course', []);
        $conditions['types'] = [];
        if (!isset($courseSetting['show_question']) || '1' === $courseSetting['show_question']) {
            $conditions['types'][] = 'question';
        }
        if (!isset($courseSetting['show_discussion']) || '1' === $courseSetting['show_discussion']) {
            $conditions['types'][] = 'discussion';
        }
        if (empty($conditions['types'])) {
            return $this->makePagingObject([], 0, $offset, $limit);
        }

        $total = $this->getCourseThreadService()->countThreads($conditions);

        $courseThreads = $this->getCourseThreadService()->searchThreads($conditions, 'postedNotStick', $offset, $limit);

        if (empty($courseThreads)) {
            return $this->makePagingObject([], 0, $offset, $limit);
        }

        $posts = $this->getCourseThreadService()->searchThreadPosts(array('threadIds' => ArrayToolkit::column($courseThreads, 'id'), 'isRead' => 0, 'exceptedUserId' => $currentUser['id']), array(), 0, PHP_INT_MAX);
        $posts = ArrayToolkit::group($posts, 'threadId');

        foreach ($courseThreads as &$thread) {
            $thread['notReadPostNum'] = isset($posts[$thread['id']]) ? count($posts[$thread['id']]) : 0;
        }

        $this->getOCUtil()->multiple($courseThreads, array('courseId'), 'course');

        return $this->makePagingObject($courseThreads, $total, $offset, $limit);
    }

    protected function getCourseThreadService()
    {
        return $this->service('Course:ThreadService');
    }
}
