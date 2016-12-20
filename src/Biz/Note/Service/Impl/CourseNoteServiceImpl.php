<?php


namespace Biz\Note\Service\Impl;


use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\System\Service\LogService;
use Biz\Note\Dao\CourseNoteDao;
use Biz\Note\Dao\CourseNoteLikeDao;
use Biz\Note\Service\CourseNoteService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Topxia\Common\ArrayToolkit;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Common\ServiceKernel;

class CourseNoteServiceImpl extends BaseService implements CourseNoteService
{
    public function getNote($id)
    {
        return $this->getNoteDao()->get($id);
    }

    public function findCourseNotesByUserIdAndTaskId($userId, $taskId)
    {
        return $this->getNoteDao()->getByUserIdAndTaskId($userId, $taskId);
    }

    public function findCourseNotesByUserIdAndCourseId($userId, $courseId)
    {
        return $this->getNoteDao()->findByUserIdAndCourseId($userId, $courseId);
    }

    public function searchNotes($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareSearchNoteConditions($conditions);
        return $this->getNoteDao()->search($conditions, $sort, $start, $limit);
    }

    public function countCourseNotes($conditions)
    {
        $conditions = $this->prepareSearchNoteConditions($conditions);
        return $this->getNoteDao()->count($conditions);
    }

    /**
     * @param array $note
     *
     * @return array
     * @throws \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function createCourseNote(array $note)
    {
        if (!ArrayToolkit::requireds($note, array('taskId', 'courseId', 'content'))) {
            throw $this->createServiceException('缺少必要的字段，保存笔记失败');
        }

        $this->getCourseService()->tryTakeCourse($note['courseId']);
        $user = $this->getCurrentUser();

        $task   = $this->getTaskService()->getTask($note['taskId']);

        if (empty($task)) {
            throw $this->createServiceException('task not found');
        }

        $note = ArrayToolkit::filter($note, array(
            'courseId' => 0,
            'taskId' => 0,
            'content'  => '',
            'status'   => 0
        ));

        $note['content'] = $this->biz['html_helper']->purify($note['content']) ?: '';
        $note['length']  = $this->calculateContentLength($note['content']);

        $existNote = $this->findCourseNotesByUserIdAndTaskId($user['id'], $note['taskId']);
        if (!$existNote) {
            $note['userId']      = $user['id'];
            $note                = $this->getNoteDao()->create($note);
            $this->dispatchEvent('course.note.create', $note);
        } else {
            unset($note['id']);
            $note                = $this->getNoteDao()->update($existNote['id'], $note);
            $this->dispatchEvent('course.note.update', new Event($note, array('preStatus' => $existNote['status'])));
        }

        $this->getCourseService()->setMemberNoteNumber(
            $note['courseId'],
            $note['userId'],
            $this->getNoteDao()->countByUserIdAndCourseId($note['userId'], $note['courseId'])
        );

        return $note;
    }

    public function deleteNote($id)
    {
        $note = $this->getNote($id);

        if (empty($note)) {
            throw $this->createNotFoundException(sprintf('笔记%s不存在，删除失败', $id));
        }

        $currentUser = $this->getCurrentUser();

        if (($note['userId'] != $currentUser['id']) && !$this->getCourseService()->isCourseTeacher($note['courseId'], 'admin_course_note')) {
            throw $this->createServiceException('你没有权限删除笔记');
        }

        $this->getNoteDao()->delete($id);

        $this->dispatchEvent('course.note.delete', $note);

        $this->getCourseService()->setMemberNoteNumber(
            $note['courseId'],
            $note['userId'],
            $this->getNoteDao()->countByUserIdAndCourseId($note['userId'], $note['courseId'])
        );

        if ($note['userId'] != $currentUser['id']) {
            $this->getLogService()->info('course', 'delete_note', "删除笔记#{$id}");
        }
    }

    public function deleteNotes(array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteNote($id);
        }
    }

    public function waveLikeNum($id, $num)
    {
        $this->getNoteDao()->wave(array($id), array(
            'likeNum' => $num
        ));
    }

    public function like($noteId)
    {
        $user = $this->getCurrentUser();
        if (empty($user)) {
            throw $this->createNotFoundException('用户还未登录,不能点赞。');
        }

        $note = $this->getNote($noteId);
        if (empty($note)) {
            throw $this->createNotFoundException('笔记不存在，或已删除。');
        }

        $like = $this->getNoteLikeByNoteIdAndUserId($noteId, $user['id']);
        if (!empty($like)) {
            throw $this->createAccessDeniedException('不可重复对一条笔记点赞！');
        }

        $noteLike = array(
            'noteId'      => $noteId,
            'userId'      => $user['id'],
            'createdTime' => time()
        );

        $this->dispatchEvent('course.note.liked', $note);

        return $this->getNoteLikeDao()->create($noteLike);
    }

    public function cancelLike($noteId)
    {
        $user = $this->getCurrentUser();
        if (empty($user)) {
            throw $this->createNotFoundException('用户还未登录,不能点赞。');
        }

        $note = $this->getNote($noteId);
        if (empty($note)) {
            throw $this->createNotFoundException('笔记不存在，或已删除。');
        }

        $this->getNoteLikeDao()->deleteByNoteIdAndUserId($noteId, $user['id']);

        $this->dispatchEvent('course.note.cancelLike', $note);
    }

    public function getNoteLikeByNoteIdAndUserId($noteId, $userId)
    {
        return $this->getNoteLikeDao()->getByNoteIdAndUserId($noteId, $userId);
    }

    public function findNoteLikesByUserId($userId)
    {
        return $this->getNoteLikeDao()->findByUserId($userId);
    }

    public function findNoteLikesByNoteId($noteId)
    {
        return $this->getNoteLikeDao()->findByNoteId($noteId);
    }

    public function findNoteLikesByNoteIds(array $noteIds)
    {
        return $this->getNoteLikeDao()->findByNoteIds($noteIds);
    }

    public function findNoteLikesByNoteIdsAndUserId(array $noteIds, $userId)
    {
        return ArrayToolkit::index($this->getNoteLikeDao()->findByNoteIdsAndUserId($noteIds, $userId), 'noteId');
    }

    protected function calculateContentLength($content)
    {
        $content = strip_tags(trim(str_replace(array("\\t", "\\r\\n", "\\r", "\\n"), '', $content)));

        return mb_strlen($content, 'utf-8');
    }

    /**
     * @param $conditions
     *
     * @return array
     * @throws \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    protected function prepareSearchNoteConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            if (!in_array($conditions['keywordType'], array('content', 'courseId', 'courseTitle'))) {
                throw $this->createServiceException('keywordType参数不正确');
            }
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
        }
        unset($conditions['keywordType']);
        unset($conditions['keyword']);

        if (isset($conditions['author'])) {
            $author               = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['userId'] = $author ? $author['id'] : -1;
            unset($conditions['author']);
        }

        return $conditions;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return CourseNoteDao
     */
    protected function getNoteDao()
    {
        return $this->biz->dao('Note:CourseNoteDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return CourseNoteLikeDao
     */
    protected function getNoteLikeDao()
    {
        return $this->biz->dao('Note:CourseNoteLikeDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}