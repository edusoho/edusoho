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
        
        $courseMember = $this->getCourseService()->getCourseMember($course['id'], $currentUser['id']);
        $this->getCourseService()->updateCourseMember($courseMember['id'], 
            array("notesNum"=>$courseMember['notesNum']+1,
                "notesLastUpdateTime"=>time()));

        $content = strip_tags($note['content']);
        $content = $this->DeleteHTML($content);
        $note['contentCount'] = mb_strlen($content,'UTF8');
        $note['createdTime'] = $note['updatedTime'] = time();
        $note['userId'] = $currentUser['id'];
        $note['status'] = self::NOTE_TYPE_PRIVATE;

		return $this->getNoteDao()->addNote($note);
	}

    public function updateNote($id,$note)
    {
        $resultNote = $this->getNote($id);
        if(empty($resultNote)) {
            throw $this->createServiceException("The Note Is Not Exist!");
        }
        if (empty($note['id'])) {
            unset($note['id']);
        }
        
        $courseMember = $this->getCourseService()->getCourseMember($resultNote['courseId'], $resultNote['userId']);
        $this->getCourseService()->updateCourseMember($courseMember['id'], array("notesLastUpdateTime"=>time()));

        $noteInfo = array_merge(array('updatedTime'=> time()),$note);
        $content = strip_tags($noteInfo['content']);
        $content = $this->DeleteHTML($content);
        $noteInfo['contentCount'] = mb_strlen($content,'UTF8');
        return $this->getNoteDao()->updateNote($id,$noteInfo);
    }

    public function findUserLessonNotes($userId,$lessonId)
    {        
        return $this->getNoteDao()->getNoteByUserIdAndLessonId($userId,$lessonId);
    }

	public function deleteNote($id)
	{
        $note = $this->tryManageNote($id);

        $this->getNoteDao()->deleteNote($id);

        $member = $this->getCourseService()->getCourseMember($note['courseId'], $note['userId']);
        if ($member) {
            $noteNumber = $this->getNoteDao()->getNoteCountByUserIdAndCourseId($note['userId'], $note['courseId']);
            $this->getCourseService()->updateCourseMember($member['id'], array(
                'notesNum' => $noteNumber,
            ));
        }
	}

    private function tryManageNote($id)
    {
        $note = $this->getNote($id);
        if (empty($note)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();
        if (empty($user->id)) {
            throw $this->createAccessDeniedException('未登录用户，无权操作！');
        }

        if (!$this->hasNoteManagerRole($note, $user)) {
            throw $this->createAccessDeniedException('您不是管理员，无权操作！');
        }
        return $note;
    }

    private function hasNoteManagerRole($note, $user) 
    {
        if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) > 0) {
            return true;
        }

        return $user['id'] == $note['userId'];
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
            $note = $this->getNoteDao()->getNote($id);
            $courseMember = $this->getCourseService()->getCourseMember($note['courseId'], $note['userId']);
            $latestNote = $this->getUserLatestNoteInCourse($note['userId'], $note['courseId']);
            if(empty($latestNote)){
                $this->getCourseService()->updateCourseMember($courseMember['id'], array(
                    "notesNum"=>$courseMember['notesNum']-1,
                    "notesLastUpdateTime"=> 0));
            } else {
                $this->getCourseService()->updateCourseMember($courseMember['id'], array(
                    "notesNum"=>$courseMember['notesNum']-1,
                    "notesLastUpdateTime"=> time()));
            }
            
            $deletedResult = $this->getNoteDao()->deleteNote($id);
            if($deletedResult == 0){
               $result = false; 
            }
        }
        return $result;
	}
    
    private function DeleteHTML($str)
    {
        $str = str_replace("<br/>","",$str);
        $str = str_replace("\\t","",$str); 
        $str = str_replace("\\r\\n","",$str); 
        $str = str_replace("\\r","",$str); 
        $str = str_replace("\\n","",$str); 
        return trim($str);
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
