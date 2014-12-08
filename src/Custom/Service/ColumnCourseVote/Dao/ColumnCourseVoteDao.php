<?php
namespace Custom\Service\ColumnCourseVote\Dao;

interface ColumnCourseVoteDao
{
	public function addColumnCourseVote(array $columnCourseVote);

	// public function updateColumn($id, array $fields);

 //    public function findColumnsByIds(array $ids);

 //    public function findColumnsByNames(array $names);

    public function findAllCourseVote($start, $limit);

    public function getColumnCourseVote($id);

    // public function getColumnByName($name);

    // public function getColumnByLikeName($name);

    // public function findAllColumnsCount();

    // public function deleteColumn($id);
     public function getAllCourseVoteCount();

    public function getColumnCourseVoteBySpecialColumnId($specialColumnId);


   // public function courseVote(array $columnCourseVote);

    public function updateCourseVoteCountByIdAndVoteCountColumn($id,$countColumn);
    
}