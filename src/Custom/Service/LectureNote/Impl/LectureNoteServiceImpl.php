<?php
namespace Custom\Service\LectureNote\Impl;

use Topxia\Service\Common\BaseService;
use Custom\Service\LectureNote\LectureNoteService;
use Topxia\Common\ArrayToolkit;

class LectureNoteServiceImpl extends BaseService implements LectureNoteService
{
    public function getLectureNote($id)
    {
        return $this->getLectureNoteDao()->getLectureNote($id);
    }

    public function findAllLectureNotes()
    {
        return $this->getLectureNoteDao()->findAllLectureNotes();
    }

    public function createLectureNote(array $field)
    {
        if (empty($field)) {
            $this->createServiceException("内容为空，创建失败！");
        }

        $lectureNote = ArrayToolkit::parts($field,array(
            'essayId','essayMaterialId','courseId','lessonId','title','type'
        ));

        $lectureNote['createdTime'] = time();
        $lectureNote['userId'] = $this->getCurrentUser()->id;

        return $this->getLectureNoteDao()->addLectureNote($lectureNote);
    }

    public function deleteLectureNote($id)
    {
        $this->getLectureNoteDao()->deleteLectureNote($id);
    }

    public function findLectureNotesByLessonIdAndType($lessonId,$type)
    {
        return $this->getLectureNoteDao()->findLectureNotesByLessonIdAndType($lessonId,$type);
    }

    private function getLectureNoteDao()
    {
        return $this->createDao('Custom:LectureNote.LectureNoteDao');
    }
}