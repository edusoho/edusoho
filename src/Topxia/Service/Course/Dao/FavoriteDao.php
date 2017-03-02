<?php

namespace Topxia\Service\Course\Dao;

interface FavoriteDao
{
    public function getFavorite($id);

    public function getFavoriteByUserIdAndCourseId($userId, $courseId, $type);

    public function findCourseFavoritesByUserId($userId, $start, $limit);

    /*
     * 2017/3/1 为移动端提供服务，其他慎用
     */
    public function findCourseFavoritesNotInClassroomByUserId($userId, $start, $limit);

    public function getFavoriteCourseCountByUserId($userId);

    public function addFavorite($collect);

    public function deleteFavorite($id);

    public function searchCourseFavoriteCount($conditions);

    public function searchCourseFavorites($conditions, $orderBy, $start, $limit);

    /*
     * 2017/3/1 为移动端提供服务，其他慎用
     */
    public function findUserFavoriteCoursesNotInClassroomWithCourseType($userId, $courseType, $start, $limit);
}