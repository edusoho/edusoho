<?php

namespace Topxia\Service\Course\Dao;

interface FavoriteDao
{

    public function getFavorite($id);

    public function getFavoriteByUserIdAndCourseId($userId, $courseId);

    public function findCourseFavoritesByUserId($userId, $start, $limit);

    public function getFavoriteCourseCountByUserId($userId);

    public function addFavorite($collect);

    public function deleteFavorite($id);

}