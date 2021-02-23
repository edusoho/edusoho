<?php

namespace Biz\Course\Job;

use Biz\Course\Service\CourseDeleteService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class DeleteCourseJob extends AbstractJob
{
    public function execute()
    {
        $args = $this->args;
        $courseId = $args['courseId'];
        $this->getCourseDeleteService()->deleteCourseChapter($courseId);

        $this->getCourseDeleteService()->deleteTaskResult($courseId);

        $this->getCourseDeleteService()->deleteCourseMember($courseId);

        $this->getCourseDeleteService()->deleteCourseNote($courseId);

        $this->getCourseDeleteService()->deleteCourseThread($courseId);

        $this->getCourseDeleteService()->deleteCourseReview($courseId);

        $this->getCourseDeleteService()->deleteCourseFavorite($courseId);

        $this->getCourseDeleteService()->deleteCourseAnnouncement($courseId);

        $this->getCourseDeleteService()->deleteCourseStatus($courseId);

        $this->getCourseDeleteService()->deleteCourseCoversation($courseId);
    }

    /**
     * @return CourseDeleteService
     */
    protected function getCourseDeleteService()
    {
        return $this->biz->service('Course:CourseDeleteService');
    }
}
