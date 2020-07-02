<?php

namespace Biz\Favorite\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface FavoriteDao extends GeneralDaoInterface
{
    public function getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);

    public function findCourseFavoritesNotInClassroomByUserId($userId, $start, $limit);

    public function findUserFavoriteCoursesNotInClassroomWithCourseType($userId, $courseType, $start, $limit);

    public function countUserFavoriteCoursesNotInClassroomWithCourseType($userId, $courseType);

    public function deleteByTargetTypeAndsTargetId($targetType, $targetId);
}
