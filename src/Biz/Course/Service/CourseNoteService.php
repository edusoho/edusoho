<?php

namespace Biz\Course\Service;

use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\CourseNoteException;
use Biz\Task\TaskException;
use Biz\User\UserException;

interface CourseNoteService
{
    const PUBLIC_STATUS = 1;
    const PRIVATE_STATUS = 0;

    /**
     * return note.
     *
     * @param int $id
     *
     * @return array
     */
    public function getNote($id);

    /**
     * @param $courseId
     *
     * @return int
     */
    public function countCourseNoteByCourseId($courseId);

    /**
     * return note by user id and task id.
     *
     * @param int $userId
     * @param int $taskId
     *
     * @return array
     */
    public function getCourseNoteByUserIdAndTaskId($userId, $taskId);

    /**
     * @param int $courseSetId
     *
     * @return array[]
     */
    public function findPublicNotesByCourseSetId($courseSetId);

    /**
     * @param int $courseId
     *
     * @return array[]
     */
    public function findPublicNotesByCourseId($courseId);

    /**
     * return notes.
     *
     * @param $userId
     * @param $courseId
     *
     * @return array[]
     */
    public function findCourseNotesByUserIdAndCourseId($userId, $courseId);

    /**
     * search notes.
     *
     * @param $conditions
     * @param $sort
     * @param $start
     * @param $limit
     *
     * @return array[]
     */
    public function searchNotes($conditions, $sort, $start, $limit);

    /**
     * @param $conditions
     *
     * @return int
     */
    public function countCourseNotes($conditions);

    /**
     * create note or update exist note.
     *
     * @param array $note
     *
     * @throws CommonException
     * @throws UserException
     * @throws TaskException
     * @throws CourseException
     *
     * @return array
     */
    public function saveNote(array $note);

    /**
     * delete note.
     *
     * @param $id
     *
     * @throws CourseNoteException
     */
    public function deleteNote($id);

    /**
     * delete notes.
     *
     * @param array $ids
     *
     * @throws CourseNoteException
     */
    public function deleteNotes(array $ids);

    /**
     * add or reduce note's like number.
     *
     * @param int $id
     * @param int $num
     */
    public function waveLikeNum($id, $num);

    /**
     * @param int $noteId
     *
     * @throws UserException
     * @throws CourseNoteException
     *
     * @return bool
     */
    public function like($noteId);

    /**
     * @param int $noteId
     *
     * @throws UserException
     * @throws CourseNoteException
     *
     * @return bool
     */
    public function cancelLike($noteId);

    /**
     * @param int $noteId
     * @param int $userId
     *
     * @return array
     */
    public function getNoteLikeByNoteIdAndUserId($noteId, $userId);

    /**
     * @param int $userId
     *
     * @return array[]
     */
    public function findNoteLikesByUserId($userId);

    /**
     * @param int $noteId
     *
     * @return array[]
     */
    public function findNoteLikesByNoteId($noteId);

    /**
     * @param array $noteIds
     *
     * @return array[]
     */
    public function findNoteLikesByNoteIds(array $noteIds);

    /**
     * @param array $noteIds
     * @param       $userId
     *
     * @return array[]
     */
    public function findNoteLikesByNoteIdsAndUserId(array $noteIds, $userId);

    /**
     * @param int $userId
     * @param int $courseId
     *
     * @return int
     */
    public function countNotesByUserIdAndCourseId($userId, $courseId);

    /**
     * @param $courseSetId
     *
     * @return int
     */
    public function countCourseNoteByCourseSetId($courseSetId);
}
