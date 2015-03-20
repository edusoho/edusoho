<?php

namespace Topxia\Service\Course\Dao;

interface ReviewDao
{
    public function getReview($id);

    public function findReviewsByCourseId($courseId, $start, $limit);

    public function getReviewCountByCourseId($courseId);

    public function getReviewByUserIdAndCourseId($userId, $courseId);

    public function getReviewRatingSumByCourseId($courseId);

    public function searchReviewsCount($conditions);

    public function searchReviews($conditions, $orderBy, $start, $limit);

    public function addReview($review);

    public function updateReview($id, $fields);

    public function deleteReview($id);

}