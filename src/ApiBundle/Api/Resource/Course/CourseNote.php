<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;

class CourseNote extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $noteId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        if ('published' !== $course['status']) {
            throw CourseException::UNPUBLISHED_COURSE();
        }
        $note = $this->getCourseNoteService()->getNote($noteId);
        $this->getOCUtil()->single($note, ['userId']);
        $this->getOCUtil()->single($note, ['taskId'], 'task');
        $user = $this->getCurrentUser();
        $note['like'] = $this->getCourseNoteService()->getNoteLikeByNoteIdAndUserId($noteId, $user['id']);

        return $note;
    }

    public function search(ApiRequest $request, $courseId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $conditions = $request->query->all();
        $conditions['courseId'] = $courseId;
        $this->getCourseService()->getCourse($courseId);
        $orderBys = $this->getSortByStr($request->query->get('sort'));
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $notes = $this->getCourseNoteService()->searchNotes($conditions, $orderBys, $offset, $limit);
        $user = $this->getCurrentUser();
        foreach ($notes as &$note) {
            $note['like'] = $this->getCourseNoteService()->getNoteLikeByNoteIdAndUserId($note['id'], $user['id']);
        }
        $count = $this->getCourseNoteService()->countCourseNotes($conditions);
        $this->getOCUtil()->multiple($notes, ['userId']);
        $this->getOCUtil()->multiple($notes, ['taskId'], 'task');

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

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
