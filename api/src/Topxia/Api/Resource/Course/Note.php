<?php

namespace Topxia\Api\Resource\Course;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class Note extends BaseResource
{
    public function get(Application $app, Request $request, $courseId, $noteId)
    {
        //$courseId暂时用不到

        $note = $this->getCourseNoteService()->getNote($noteId);
        if (empty($note)) {
            return $this->error('500', "ID为{$noteId}的笔记不存在");
        }
        return $this->filter($note);
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
