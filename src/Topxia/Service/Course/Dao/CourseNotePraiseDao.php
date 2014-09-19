<?php
namespace Topxia\Service\Course\Dao;

interface CourseNotePraiseDao
{
	public function getNotePraise($id);

	public function getNotePraiseByNoteIdAndUserId($noteId,$userId);

	public function addNotePraise($notePraise);

	public function deleteNotePraiseByNoteIdAndUserId($noteId,$userId);

	public function findNotePraisesByUserId($userId);

	public function findNotePraisesByNoteId($noteId);

}