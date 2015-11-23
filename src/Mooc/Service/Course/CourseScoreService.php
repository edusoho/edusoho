<?php

namespace Mooc\Service\Course;

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

    /**
     * 删除课程分数设置和课程学员分数
     * @param $courseId
     * @return mixed
     */
    public function deleteCourseScoreByCourseId($courseId);

}
