<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\NoteService;
use Topxia\Service\Common\ServiceEvent;

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

    public function searchNotes($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->prepareSearchNoteConditions($conditions);

        return $this->getNoteDao()->searchNotes($conditions, $orderBy, $start, $limit);
    }

    public function searchNoteCount($conditions)
    {
        $conditions = $this->prepareSearchNoteConditions($conditions);

        return $this->getNoteDao()->searchNoteCount($conditions);
    }

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
     * 类似这样的，提交数据保存到数据的流程是：
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
        $user                  = $this->getCurrentUser();

        if (!$this->getCourseService()->getCourseLesson($note['courseId'], $note['lessonId'])) {
            throw $this->createServiceException('课时不存在，保存笔记失败');
        }

        $note = ArrayToolkit::filter($note, array(
            'courseId' => 0,
            'lessonId' => 0,
            'content'  => '',
            'status'   => 0
        ));

        $note['content'] = $this->purifyHtml($note['content']) ?: '';
        $note['length']  = $this->calculateContnentLength($note['content']);

        $existNote = $this->getUserLessonNote($user['id'], $note['lessonId']);
        if (!$existNote) {
            $note['userId']      = $user['id'];
            $note['createdTime'] = time();
            $note['updatedTime'] = time();
            $note                = $this->getNoteDao()->addNote($note);
            $this->getDispatcher()->dispatch('course.note.create', new ServiceEvent($note));
        } else {
            $note['updatedTime'] = time();
            $note                = $this->getNoteDao()->updateNote($existNote['id'], $note);
            $this->getDispatcher()->dispatch('course.note.update', new ServiceEvent($note, array('preStatus' => $existNote['status'])));
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
        $this->getDispatcher()->dispatch('course.note.delete', new ServiceEvent($note));

        $this->getCourseService()->setMemberNoteNumber(
            $note['courseId'],
            $note['userId'],
            $this->getNoteDao()->getNoteCountByUserIdAndCourseId($note['userId'], $note['courseId'])
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

    public function count($id, $field, $diff)
    {
        $this->getNoteDao()->count($id, $field, $diff);
    }

    public function like($noteId)
    {
        $user = $this->getCurrentUser();
        if (empty($user)) {
            throw $this->createNotFoundException("用户还未登录,不能点赞。");
        }

        $note = $this->getNote($noteId);
        if (empty($note)) {
            throw $this->createNotFoundException("笔记不存在，或已删除。");
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

        $this->getDispatcher()->dispatch('course.note.liked', new ServiceEvent($note));

        return $this->getNoteLikeDao()->addNoteLike($noteLike);
    }

    public function cancelLike($noteId)
    {
        $user = $this->getCurrentUser();
        if (empty($user)) {
            throw $this->createNotFoundException("用户还未登录,不能点赞。");
        }

        $note = $this->getNote($noteId);
        if (empty($note)) {
            throw $this->createNotFoundException("笔记不存在，或已删除。");
        }

        $this->getNoteLikeDao()->deleteNoteLikeByNoteIdAndUserId($noteId, $user['id']);

        $this->getDispatcher()->dispatch('course.note.cancelLike', new ServiceEvent($note));
    }

    public function getNoteLikeByNoteIdAndUserId($noteId, $userId)
    {
        return $this->getNoteLikeDao()->getNoteLikeByNoteIdAndUserId($noteId, $userId);
    }

    public function findNoteLikesByUserId($userId)
    {
        return $this->getNoteLikeDao()->findNoteLikesByUserId($userId);
    }

    public function findNoteLikesByNoteId($noteId)
    {
        return $this->getNoteLikeDao()->findNoteLikesByNoteId($noteId);
    }

    public function findNoteLikesByNoteIds(array $noteIds)
    {
        return $this->getNoteLikeDao()->findNoteLikesByNoteIds($noteIds);
    }

    public function findNoteLikesByNoteIdsAndUserId(array $noteIds, $userId)
    {
        return ArrayToolkit::index($this->getNoteLikeDao()->findNoteLikesByNoteIdsAndUserId($noteIds, $userId), 'noteId');
    }

    // @todo HTML Purifier
    protected function calculateContnentLength($content)
    {
        $content = strip_tags(trim(str_replace(array("\\t", "\\r\\n", "\\r", "\\n"), '', $content)));

        return mb_strlen($content, 'utf-8');
    }

    protected function getNoteDao()
    {
        return $this->createDao('Course.CourseNoteDao');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getNoteLikeDao()
    {
        return $this->createDao('Course.CourseNoteLikeDao');
    }
}
