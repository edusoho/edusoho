<?php

namespace Custom\Service\Course;

interface CourseScoreService
{
    public function getUserScoreByUserIdAndCourseId($userId, $courseId);

    public function updateUserCourseScore($id, $score);

    public function addUserCourseScore($score);

    public function searchMemberScoreCount($conditions);

    public function searchMemberScore($conditions, $orderBy, $start, $limit);

    public function findUsersScoreBySqlJoinUser($fields);

    public function findAllMemberScore($courseId);

    public function findUserScoreByIdsAndCourseId($userIds, $courseId);

    public function getCoursePassStudentCount($courseId);

    /**
     * CourseScoreSetting
     */
    public function addScoreSetting($scoreSetting);

    public function updateScoreSetting($courseId, $fields);

    public function getScoreSettingByCourseId($courseId);

    public function findScoreSettingsByCourseIds($courseIds);

}
