<?php

namespace Classroom\Service\Classroom\Dao;

interface ClassroomReviewDao
{
    public function getReview($id);

    public function searchReviews($conditions, $orderBy, $start, $limit);

    public function searchReviewCount($conditions);

    public function getReviewByUserIdAndClassroomId($userId, $classroomId);

    public function addReview($review);

    public function updateReview($id, $fields);

    public function getReviewRatingSumByClassroomId($classroomId);

    public function getReviewCountByClassroomId($classroomId);

    public function deleteReview($id);
}
