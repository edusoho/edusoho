<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\NoteService;

class NoteServiceImpl extends BaseService implements NoteService
{
    public function findUserCourseNotes($userId, $courseId)
    {   
        $course = $this->getCourseService()->getCourse($courseId);
        if(empty($course)){
            throw $this->createServiceException("The Course Is Not Exist!");
        }
        $user = $this->getUserService()->getUser($userId);
        if(empty($user)){
            throw $this->createServiceException("The User Is Not Exist!");
        }
        return $this->getNoteDao()->findNotesByUserIdAndCourseId($user['id'], $course['id']);
    }

	public function getNote($id)
	{
		$note = $this->getNoteDao()->getNote($id);
		if(empty($note)) {
            throw $this->createServiceException("The Note Is Not Exist!");
        }
        return $note;
    }

	public function saveNote(array $note)
	{
		$currentUser = $this->getCurrentUser();
        $result = $this->findUserLessonNotes($currentUser['id'], $note['lessonId']);
        if(!$result){
            return $this->addNote($note);
        } else {
            return $this->updateNote($result['id'], $note);
        }
	}

	public function addNote($note)
	{
		$currentUser = $this->getCurrentUser();

        /*添加的时候允许笔记内容为空*/
        if(empty($note['content'])){
            $note['content'] = '';
        }

        $course = $this->getCourseService()->getCourse($note['courseId']);
        if(empty($course)){
            throw $this->createServiceException("The Course Is Not Exist!");
        }

        $lesson = $this->getCourseService()->getCourseLesson($note['courseId'], $note['lessonId']);
        if(empty($lesson)){
            throw $this->createServiceException("The Lesson Is Not Exist!");
        }

        $resultNote = $this->getNoteDao()->getNoteByUserIdAndLessonId($currentUser['id'],$note['lessonId']);
		if ($resultNote) {
            throw $this->createServiceException('The Note Is Already Exist!');	
        }

        $content = strip_tags($note['content']);
        $note['contentCount'] = mb_strlen($content,'UTF8');
        $note['createdTime'] = $note['updatedTime'] = time();
        $note['userId'] = $currentUser['id'];
        $note['status'] = self::NOTE_TYPE_PRIVATE;

		return $this->getNoteDao()->addNote($note);
	}

    //TODO 只允许更新笔记的标题，内容，不允许更新所属的课程和课时
    public function updateNote($id,$note)
    {
        $resultNote = $this->getNote($id);
        if(empty($resultNote)) {
            throw $this->createServiceException("The Note Is Not Exist!");
        }
        if (empty($note['id'])) {
            unset($note['id']);
       }
        $noteInfo = array_merge(array('updatedTime'=> time()),$note);
        $content = strip_tags($noteInfo['content']);
        $noteInfo['contentCount'] = mb_strlen($content,'UTF8');
        return $this->getNoteDao()->updateNote($id,$noteInfo);
    }

    public function findUserLessonNotes($userId,$lessonId)
    {        
        return $this->getNoteDao()->getNoteByUserIdAndLessonId($userId,$lessonId);
    }

	public function deleteNote($id)
	{
        $resultNote = $this->getNote($id);
        if(empty($resultNote)) {
            throw $this->createServiceException("The Note Is Not Exist");
        }
		return $this->getNoteDao()->deleteNote($id);
	}

	public function searchNotes($conditions, $sort, $start, $limit)
	{
		switch ($sort) {
			case 'created':
				$orderBys = array(
                    array('createdTime', 'DESC'),
                );
				break;
            case 'update':
                $orderBys = array(
                    array('updatedTime', 'DESC'),
                );
                break;
			default:
				throw $this->createServiceException('参数sort不正确。');
		}
		return $this->getNoteDao()->searchNotes($conditions, $orderBys, $start, $limit);
	}


	public function searchNotesCount($conditions)
	{
		return $this->getNoteDao()->searchNotesCount($conditions);
	}
    
	public function deleteNotes($ids)
	{
        $result = true;
        if(empty($ids)){
             throw $this->createServiceException("Please select thread item !");
        }

       	foreach ($ids as $id) {
            $deletedResult = $this->getNoteDao()->deleteNote($id);
            if($deletedResult == 0){
               $result = false; 
            }
        }
        return $result;
	}

    private function getNoteDao()
    {
    	return $this->createDao('Course.CourseNoteDao');
    }

   private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
