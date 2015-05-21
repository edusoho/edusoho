<?php

namespace Classroom\Service\Classroom;

interface ClassroomReviewService
{
    public function getReview($id);

    public function searchReviews($conditions, $orderBy, $start, $limit);

    public function searchReviewCount($condtions);

    public function getUserClassroomReview($userId, $classroomId);

    public function saveReview($fields);

    public function deleteReview($id);
}
