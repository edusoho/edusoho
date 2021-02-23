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

        $this->deleteTaskResult($courseId);

        $this->deleteCourseMember($courseId);

        $this->deleteCourseNote($courseId);

        $this->deleteCourseThread($courseId);

        $this->deleteCourseReview($courseId);

        $this->deleteCourseFavorite($courseId);

        $this->deleteCourseAnnouncement($courseId);

        $this->deleteCourseStatus($courseId);

        $this->deleteCourseCoversation($courseId);
    }

    /**
     * @return CourseDeleteService
     */
    protected function getCourseDeleteService()
    {
        return $this->biz->service('Course:CourseDeleteService');
    }
}
