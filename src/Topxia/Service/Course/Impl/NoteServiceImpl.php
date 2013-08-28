<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\NoteService;
use Topxia\Common\ArrayToolkit;

class NoteServiceImpl extends BaseService implements NoteService
{
	public function getNote($id)
	{
		return $this->getNoteDao()->getNote($id);
    }

    public function getUserLessonNote($userId, $lessonId)
    {
        return $this->getNoteDao()->getNoteByUserIdAndLessonId($userId, $lessonId);
    }

    public function findUserCourseNotes($userId, $courseId)
    {   
        return $this->getNoteDao()->findNotesByUserIdAndCourseId($userId, $courseId);
    }

    public function searchNotes($conditions, $sort, $start, $limit)
    {
        switch ($sort) {
            case 'created':
                $orderBy = array('createdTime', 'DESC');
                break;
            case 'updated':
                $orderBy =  array('updatedTime', 'DESC');
                break;
            default:
                throw $this->createServiceException('参数sort不正确。');
        }

        $conditions = $this->prepareSearchNoteConditions($conditions);
        return $this->getNoteDao()->searchNotes($conditions, $orderBy, $start, $limit);
    }

    public function searchNoteCount($conditions)
    {
        $conditions = $this->prepareSearchNoteConditions($conditions);
        return $this->getNoteDao()->searchNoteCount($conditions);
    }

    private function prepareSearchNoteConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (isset($conditions['author'])) {
            $author = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['userId'] = $author ? $author['id'] : -1;
            unset($conditions['author']);
        }

        return $conditions;
    }

    /**
     *类似这样的，提交数据保存到数据的流程是：
     *
     *  1. 检查参数是否正确，不正确就抛出异常
     *  2. 过滤数据
     *  3. 插入到数据库
     *  4. 更新其他相关的缓存字段
     */
	public function saveNote(array $note)
	{
		$user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }
        
        if (!ArrayToolkit::requireds($note, array('lessonId', 'courseId', 'content'))) {
            throw $this->createServiceException('缺少必要的字段，保存笔记失败');
        }


        $lesson = $this->getCourseService()->getCourseLesson($note['courseId'], $note['lessonId']);
        if(empty($lesson)){
            throw $this->createServiceException('课时不存在，保存笔记失败');
        }
        $existNote = $this->getUserLessonNote($user['id'], $note['lessonId']);

        $fields = array();
        //保存笔记,过滤html
        $note['content'] = $this->purifyHtml($note['content']);
        $fields['content'] = empty($note['content']) ? '' : $note['content'];
        $fields['length'] = $this->calculateContnentLength($note['content']);
        $fields['courseId'] = $lesson['courseId'];
        $fields['lessonId'] = $lesson['id'];

        if (!$existNote) {
            $fields['userId'] = $user['id'];
            $fields['createdTime'] = time();
            $note = $this->getNoteDao()->addNote($fields);
        } else {
            $fields['updatedTime'] = time();
            $note = $this->getNoteDao()->updateNote($existNote['id'], $fields);
        }
        $member = $this->getCourseService()->getCourseMember($note['courseId'], $user['id']);

        if ($member) {
            $memberFields = array();
            $memberFields['noteLastUpdateTime'] = time();
            if (!$existNote) {
                $memberFields['noteNum'] = $this->getNoteDao()->getNoteCountByUserIdAndCourseId($note['userId'], $note['courseId']);
            }
            $this->getCourseService()->updateCourseMember($member['id'], $memberFields);
        }

        return $note;
	}

	public function deleteNote($id)
	{
        $note = $this->tryManageNote($id);

        $this->getNoteDao()->deleteNote($id);

        $member = $this->getCourseService()->getCourseMember($note['courseId'], $note['userId']);
        if ($member) {
            $noteNumber = $this->getNoteDao()->getNoteCountByUserIdAndCourseId($note['userId'], $note['courseId']);
            $this->getCourseService()->updateCourseMember($member['id'], array(
                'noteNum' => $noteNumber,
            ));
        }
	}

    public function deleteNotes(array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteNote($id);
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

    // @todo HTML Purifier
    private function calculateContnentLength($content)
    {
        $content = strip_tags(trim(str_replace(array("\\t", "\\r\\n", "\\r", "\\n"), '',$content)));
        return mb_strlen($content, 'utf-8');
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
