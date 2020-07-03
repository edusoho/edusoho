<?php

namespace Biz\Review\Service;

interface ReviewService
{
    public function getReview($id);

    public function createReview($review);

    public function tryCreateReview($review);

    public function updateReview($id, $review);

    public function deleteReview($id);

    public function countReviews($conditions);

    public function searchReviews($conditions, $orderBys, $start, $limit, $columns = []);

    public function countRatingByTargetTypeAndTargetId($targetType, $targetId);

    public function countRatingByTargetTypeAndTargetIds($targetType, $targetIds);

    public function getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);
}
