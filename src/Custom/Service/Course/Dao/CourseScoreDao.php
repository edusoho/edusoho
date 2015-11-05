<?php
namespace Custom\Service\Course\Dao;


interface CourseScoreDao
{
	public function getUserCourseScore($id);
	
    public function getUserScoreByUserIdAndCourseId($userId,$courseId);

    public function addUserCourseScore($score);

    public function updateUserCourseScore($id,$score);

    public function searchMemberScoreCount($conditions);

    public function searchMemberScore($conditions,$orderBy,$start, $limit);

    public function findUsersScoreBySqlJoinUser($fields);

    public function findAllMemberScore($courseId);

    public function findUserScoreByIdsAndCourseId($userIds,$courseId);
}