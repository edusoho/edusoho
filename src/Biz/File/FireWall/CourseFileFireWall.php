<?php

namespace Biz\File\FireWall;

class CourseFileFireWall extends BaseFireWall implements FireWallInterface
{
    public function canAccess($attachment)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }

        $targetTypes = explode('.', $attachment['targetType']);
        $type = array_pop($targetTypes);

        if ('thread' === $type) {
            $thread = $this->getThreadService()->getThread($courseId = null, $attachment['targetId']);

            if ($user['id'] == $thread['userId']) {
                return true;
            }
            $course = $this->getCourseService()->getCourse($thread['courseId']);

            if (is_array($course['teacherIds']) && in_array($user['id'], $course['teacherIds'])) {
                return true;
            }
        } elseif ('post' === $type) {
            $post = $this->getThreadService()->getPost($courseId = null, $attachment['targetId']);
            if ($user['id'] == $post['userId']) {
                return true;
            }
            $thread = $this->getThreadService()->getThread($courseId = null, $post['threadId']);
            if ($user['id'] == $thread['userId']) {
                return true;
            }
            $course = $this->getCourseService()->getCourse($thread['courseId']);
            if (is_array($course['teacherIds']) && in_array($user['id'], $course['teacherIds'])) {
                return true;
            }
        }

        return false;
    }

    protected function getThreadService()
    {
        return $this->biz->service('Course:ThreadService');
    }

    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
