<?php

namespace Topxia\Service\FIle\FireWall;

use Topxia\Service\Common\ServiceKernel;

class CourseFileFireWall
{
    public function canAccess($attachment)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }

        $targetTypes = explode('.', $attachment['targetType']);
        $type        = array_pop($targetTypes);
        if ($type === 'thread') {
            $thread = $this->getThreadService()->getThread($courseId = null, $attachment['targetId']);

            if ($user['id'] == $thread['userId']) {
                return true;
            }
            $course = $this->getCourseService()->getCourse($thread['courseId']);

            if (array_key_exists($user['id'], $course['teacherIds'])) {
                return true;
            }
        } elseif ($type === 'post') {
            $post = $this->getThreadService()->getPost($courseId = null, $attachment['targetId']);
            if ($user['id'] == $post['userId']) {
                return true;
            }
            $thread = $this->getThreadService()->getThread($courseId = null, $post['threadId']);
            if ($user['id'] == $thread['userId']) {
                return true;
            }
            $course = $this->getCourseService()->getCourse($thread['courseId']);
            if (array_key_exists($user['id'], $course['teacherIds'])) {
                return true;
            }
        }
        return false;
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

    public function getCurrentUser()
    {
        return $this->getKernel()->getCurrentUser();
    }

    protected function getThreadService()
    {
        return $this->getKernel()->createService('Course.ThreadService');
    }

    protected function getCourseService()
    {
        return $this->getKernel()->createService('Course.CourseService');
    }
}
