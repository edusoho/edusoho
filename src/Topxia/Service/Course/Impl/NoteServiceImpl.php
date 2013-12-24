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

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            if (!in_array($conditions['keywordType'], array('content', 'courseId'))) {
                throw $this->createServiceException('keywordType参数不正确');
            }
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
        }
        unset($conditions['keywordType']);
        unset($conditions['keyword']);

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
        if (!ArrayToolkit::requireds($note, array('lessonId', 'courseId', 'content'))) {
            throw $this->createServiceException('缺少必要的字段，保存笔记失败');
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($note['courseId']);
        $user = $this->getCurrentUser();

        if(!$this->getCourseService()->getCourseLesson($note['courseId'], $note['lessonId'])) {
            throw $this->createServiceException('课时不存在，保存笔记失败');
        }

        $note = ArrayToolkit::filter($note, array(
            'courseId' => 0,
            'lessonId' => 0,
            'content' => '',
        ));

        $note['content'] = $this->purifyHtml($note['content']) ? : '';
        $note['length'] = $this->calculateContnentLength($note['content']);

        $existNote = $this->getUserLessonNote($user['id'], $note['lessonId']);
        if (!$existNote) {
            $note['userId'] = $user['id'];
            $note['createdTime'] = time();
            $note = $this->getNoteDao()->addNote($note);
        } else {
            $note['updatedTime'] = time();
            $note = $this->getNoteDao()->updateNote($existNote['id'], $note);
        }

        $this->getCourseService()->setMemberNoteNumber(
            $note['courseId'],
            $note['userId'], 
            $this->getNoteDao()->getNoteCountByUserIdAndCourseId($note['userId'], $note['courseId'])
        );

        return $note;
	}

	public function deleteNote($id)
	{
        $note = $this->getNote($id);
        if (empty($note)) {
            throw $this->createServiceException("笔记(#{$id})不存在，删除失败");
        }

        $currentUser = $this->getCurrentUser();
        if (($note['userId'] != $currentUser['id']) && !$this->getCourseService()->canManageCourse($note['courseId'])) {
            throw $this->createServiceException("你没有权限删除笔记(#{$id})");
        }

        $this->getNoteDao()->deleteNote($id);

        $this->getCourseService()->setMemberNoteNumber(
            $note['courseId'],
            $note['userId'], 
            $this->getNoteDao()->getNoteCountByUserIdAndCourseId($note['userId'], $note['courseId'])
        );

        if ($note['userId'] != $currentUser['id']) {
            $this->getLogService()->info('note', 'delete', "删除笔记#{$id}");
        }
	}

    public function deleteNotes(array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteNote($id);
        }
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

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
