<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;

class CourseChapter extends AbstractResource
{
    /**
     * post /api/course/{courseId}/chapter
     *
     */
    public function add(ApiRequest $request, $courseId)
    {
        if (!$this->getCourseService()->hasCourseManagerRole($courseId, 'course_lesson_manage')) {
            throw CourseException::FORBIDDEN_MANAGE_COURSE();
        }

        $chapterInfo = ArrayToolkit::parts($request->request->all(), ['type', 'title']);
        $chapterInfo['courseId'] = $courseId;

        return $this->getCourseService()->createChapter($chapterInfo);
    }

    /**
     * patch /api/course/{courseId}/chapter/{chapterId}
     *
     */
    public function update(ApiRequest $request, $courseId, $chapterId)
    {
        if (!$this->getCourseService()->hasCourseManagerRole($courseId, 'course_lesson_manage')) {
            throw CourseException::FORBIDDEN_MANAGE_COURSE();
        }

        $chapterInfo = ArrayToolkit::parts($request->request->all(), ['type', 'title']);

        return $this->getCourseService()->updateChapter($courseId, $chapterId, $chapterInfo);
    }

    /**
     * delete /api/course/{courseId}/chapter/{chapterId}
     */
    public function remove(ApiRequest $request, $courseId, $chapterId)
    {
        if (!$this->getCourseService()->hasCourseManagerRole($courseId, 'course_lesson_manage')) {
            throw CourseException::FORBIDDEN_MANAGE_COURSE();
        }

        $chapter = $this->getCourseService()->getChapter($courseId, $chapterId);
        if (empty($chapter)) {
            throw CourseException::NOTFOUND_CHAPTER();
        }

        $this->getCourseService()->deleteChapter(
            $courseId,
            $chapterId
        );

        return ['success' => true];
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
