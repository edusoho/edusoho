<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class CourseNotes extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $note = $this->getCourseNoteService()->getNote($id);
        if (empty($note)) {
            return $this->error('500', "ID为{$id}的笔记不存在");
        }
        return $this->filter($note);
    }

    public function search(Application $app, Request $request)
    {
        $fields = $request->query->all();

        $conditions = ArrayToolkit::parts($fields, array('userId', 'courseId', 'lessonId'));
        if (empty($conditions)) {
            throw new \Exception("userId/courseId/lessonId必须指定其中之一");
        }
        
        $total = $this->getCourseNoteService()->searchNoteCount($conditions);
        $notes = $this->getCourseNoteService()->searchNotes(
            $conditions,
            array('createdTime' => 'DESC'),
            isset($fields['start']) ? (int) $fields['start'] : 0,
            isset($fields['limit']) ? (int) $fields['limit'] : 20
        );

        return $this->wrap($this->multicallFilter('CourseNotes', $notes), $total);
    }

    public function post(Application $app, Request $request)
    {
        $requiredFields = array('lessonId', 'content');
        $fields = $this->checkRequiredFields($requiredFields, $request->request->all());

        $lesson = $this->getCourseService()->getLesson($fields['lessonId']);
        if (empty($lesson)) {
            throw new \Exception("ID为{$lessonId}的课时不存在");
        }

        $note = array(
            'courseId' => $lesson['courseId'],
            'lessonId' => $fields['lessonId'],
            'status' => !empty($fields['status']) ? 1 : 0,
            'content' => $fields['content'],
        );
        $note = $this->getCourseNoteService()->saveNote($note);
        return $this->filter($note);
    }

    public function filter($res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);
        return $res;
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
