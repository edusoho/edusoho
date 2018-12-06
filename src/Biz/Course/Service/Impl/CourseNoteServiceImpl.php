<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\CourseNoteException;
use Biz\Course\Dao\CourseNoteDao;
use Biz\Course\Dao\CourseNoteLikeDao;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Codeages\Biz\Framework\Event\Event;
use AppBundle\Common\ArrayToolkit;

class CourseNoteServiceImpl extends BaseService implements CourseNoteService
{
    public function getNote($id)
    {
        return $this->getNoteDao()->get($id);
    }

    /**
     * @param $courseId
     *
     * @return mixed
     */
    public function countCourseNoteByCourseId($courseId)
    {
        return $this->countCourseNotes(array(
            'courseId' => $courseId,
            'status' => CourseNoteService::PUBLIC_STATUS,
        ));
    }

    public function getCourseNoteByUserIdAndTaskId($userId, $taskId)
    {
        return $this->getNoteDao()->getByUserIdAndTaskId($userId, $taskId);
    }

    public function findPublicNotesByCourseSetId($courseSetId)
    {
        $conditions = array(
            'courseSetId' => $courseSetId,
            'status' => 1,
        );

        return $this->searchNotes(
            $conditions,
            array(
                'createdTime' => 'DESC',
            ),
            0,
            $this->countCourseNotes($conditions)
        );
    }

    /**
     * @param int $courseId
     *
     * @return mixed
     */
    public function findPublicNotesByCourseId($courseId)
    {
        $conditions = array(
            'courseId' => $courseId,
            'status' => CourseNoteService::PUBLIC_STATUS,
        );

        return $this->searchNotes(
            $conditions,
            array(
                'createdTime' => 'DESC',
            ),
            0,
            $this->countCourseNotes($conditions)
        );
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

    public function saveNote(array $note)
    {
        if (!ArrayToolkit::requireds($note, array('taskId', 'courseId', 'content'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $this->getCourseService()->tryTakeCourse($note['courseId']);

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $task = $this->getTaskService()->getTask($note['taskId']);

        if (empty($task)) {
            $this->createNewException(TaskException::NOTFOUND_TASK());
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        } else {
            $note['courseSetId'] = $course['courseSetId'];
        }

        $note = ArrayToolkit::filter($note, array(
            'courseId' => 0,
            'courseSetId' => 0,
            'taskId' => 0,
            'content' => '',
            'status' => 0,
        ));

        $note['content'] = $this->purifyHtml($note['content']) ?: '';
        $note['length'] = $this->calculateContentLength($note['content']);

        $existNote = $this->getCourseNoteByUserIdAndTaskId($user['id'], $note['taskId']);
        if (!$existNote) {
            $note['userId'] = $user['id'];
            $note = $this->getNoteDao()->create($note);
            $this->dispatchEvent('course.note.create', $note);
        } else {
            unset($note['id']);
            $note = $this->getNoteDao()->update($existNote['id'], $note);
            $this->dispatchEvent('course.note.update', new Event($note, array('preStatus' => $existNote['status'])));
        }

        return $note;
    }

    public function deleteNote($id)
    {
        $note = $this->getNote($id);

        if (empty($note)) {
            $this->createNewException(CourseNoteException::NOTFOUND_NOTE());
        }

        $currentUser = $this->getCurrentUser();

        if (!$this->hasPermission($currentUser, $note)) {
            $this->createNewException(CourseNoteException::NO_PERMISSION());
        }

        $this->getNoteDao()->delete($id);

        $this->dispatchEvent('course.note.delete', $note);

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
            'likeNum' => $num,
        ));
    }

    public function like($noteId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $note = $this->getNote($noteId);
        if (empty($note)) {
            $this->createNewException(CourseNoteException::NOTFOUND_NOTE());
        }

        $like = $this->getNoteLikeByNoteIdAndUserId($noteId, $user['id']);

        if (!empty($like)) {
            $this->createNewException(CourseNoteException::DUPLICATE_LIKE());
        }

        $noteLike = array(
            'noteId' => $noteId,
            'userId' => $user['id'],
            'createdTime' => time(),
        );

        $this->dispatchEvent('course.note.liked', $note);
        $like = $this->getNoteLikeDao()->create($noteLike);

        return !empty($like);
    }

    public function cancelLike($noteId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $note = $this->getNote($noteId);
        if (empty($note)) {
            $this->createNewException(CourseNoteException::NOTFOUND_NOTE());
        }

        $this->getNoteLikeDao()->deleteByNoteIdAndUserId($noteId, $user['id']);

        $this->dispatchEvent('course.note.cancelLike', $note);

        return true;
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

    public function countNotesByUserIdAndCourseId($userId, $courseId)
    {
        return $this->getNoteDao()->countByUserIdAndCourseId($userId, $courseId);
    }

    private function hasPermission($currentUser, $note)
    {
        return $note['userId'] == $currentUser['id']
            ||
            $this->getCourseMemberService()->isCourseTeacher($note['courseId'], $currentUser['id'])
            || $currentUser->isAdmin();
    }

    /**
     * @param $courseSetId
     *
     * @return int
     */
    public function countCourseNoteByCourseSetId($courseSetId)
    {
        return $this->countCourseNotes(array(
            'courseSetId' => $courseSetId,
            'status' => CourseNoteService::PUBLIC_STATUS,
        ));
    }

    protected function calculateContentLength($content)
    {
        $content = strip_tags(trim(str_replace(array('\\t', '\\r\\n', '\\r', '\\n'), '', $content)));

        return mb_strlen($content, 'utf-8');
    }

    /**
     * @param $conditions
     *
     * @return array
     *
     * @throws CommonException
     * @throws \Exception
     */
    protected function prepareSearchNoteConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            if (!in_array($conditions['keywordType'], array('content', 'courseId', 'courseSetId', 'courseTitle'))) {
                $this->createNewException(CommonException::ERROR_PARAMETER());
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
        return $this->biz->dao('Course:CourseNoteDao');
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
        return $this->biz->dao('Course:CourseNoteLikeDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}
