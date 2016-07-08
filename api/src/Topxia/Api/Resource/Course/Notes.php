<?php

namespace Topxia\Api\Resource\Course;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class Notes extends BaseResource
{
    public function get(Application $app, Request $request, $courseId)
    {
        $fields = $request->query->all();

        $conditions = ArrayToolkit::parts($fields, array('userId', 'lessonId'));
        if (empty($conditions)) {
            throw new \Exception("userId/lessonId必须指定其中之一");
        }
        
        $conditions['courseId'] = $courseId;

        $total = $this->getCourseNoteService()->searchNoteCount($conditions);
        $notes = $this->getCourseNoteService()->searchNotes(
            $conditions,
            array('createdTime' => 'DESC'),
            isset($fields['start']) ? (int) $fields['start'] : 0,
            isset($fields['limit']) ? (int) $fields['limit'] : 20
        );

        return $this->wrap($this->filter($notes), $total);
    }

    public function post(Application $app, Request $request, $courseId)
    {
        $requiredFields = array('lessonId', 'content');
        $fields = $this->checkRequiredFields($requiredFields, $request->request->all());

        $lesson = $this->getCourseService()->getLesson($fields['lessonId']);
        if (empty($lesson)) {
            throw new \Exception("课时#{$fields['lessonId']}不存在");
        }

        if ($courseId != $lesson['courseId']) {
            throw new \Exception("课时#{$fields['lessonId']}不属于课程#{$courseId}");
        }

        $note = array(
            'courseId' => $courseId,
            'lessonId' => $fields['lessonId'],
            'status' => !empty($fields['status']) ? 1 : 0,
            'content' => $fields['content']
        );
        $note = $this->getCourseNoteService()->saveNote($note);
        return $this->callFilter('Course/Note', $note);
    }

    public function filter($res)
    {
        return $this->multicallFilter('Course/Note', $res);
    }

    protected function getCourseNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
