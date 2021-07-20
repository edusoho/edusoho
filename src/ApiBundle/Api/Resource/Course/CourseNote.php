<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\CourseService;

class CourseNote extends AbstractResource
{
    public function search(ApiRequest $request, $courseId)
    {
        $conditions = $request->query->all();
        $conditions['courseId'] = $courseId;
        $this->getCourseService()->getCourse($courseId);
        $orderBys = $this->getSortByStr($request->query->get('sort'));
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $notes = $this->getCourseNoteService()->searchNotes($conditions, $orderBys, $offset, $limit);
        $count = $this->getCourseNoteService()->countCourseNotes($conditions);

        return $this->makePagingObject($notes, $count, $offset, $limit);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->getBiz()->service('Course:CourseNoteService');
    }
}
