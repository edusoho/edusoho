<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

class CourseChapter extends AbstractResource
{
    /**
     * post /api/course/{courseId}/chapter
     *
     */
    public function add(ApiRequest $request, $courseId)
    {
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
        $chapterInfo = ArrayToolkit::parts($request->request->all(), ['type', 'title']);

        return $this->getCourseService()->updateChapter($courseId, $chapterId, $chapterInfo);
    }

    /**
     * delete /api/course/{courseId}/chapter/{chapterId}
     *
     */
    public function remove(ApiRequest $request, $courseId, $chapterId)
    {
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
