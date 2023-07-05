<?php

namespace Biz\AuditCenter\ContentAuditSources;

use Biz\Course\Dao\ThreadDao;
use Biz\Course\Service\ThreadService;

class CourseThread extends AbstractSource
{
    public function handleSource($audit)
    {
        $thread = $this->getCourseThreadService()->getThreadByThreadId($audit['targetId']);
        if (empty($thread)) {
            return;
        }

        $course = $this->getCourseService()->getCourse($thread['courseId']);
        if (empty($course)) {
            return;
        }

        $fields = $this->getAuditFields($audit);

        if (!empty($fields)) {
            $this->getCourseThreadDao()->update($thread['id'], $fields);
            if ('illegal' == $fields['auditStatus']) {
                if ('course_thread' == $audit['targetType']) {
                    $this->getCourseService()->updateCourse($course['id'], ['discussionNum' => $course['discussionNum'] - 1]);
                } else {
                    $this->getCourseService()->updateCourse($course['id'], ['questionNum' => $course['questionNum'] - 1]);
                }
            }
        }
    }

    /**
     * @return ThreadService
     */
    protected function getCourseThreadService()
    {
        return $this->biz->service('Course:ThreadService');
    }

    /**
     * @return ThreadDao
     */
    protected function getCourseThreadDao()
    {
        return $this->biz->dao('Course:ThreadDao');
    }

    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
