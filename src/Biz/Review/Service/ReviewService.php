<?php

namespace Biz\Review\Service;

interface ReviewService
{
    public function getReview($id);

    public function createReview($review);

    public function tryCreateReview($review);

    public function updateReview($id, $review);

    public function deleteReview($id);

    public function countReview($conditions);

    public function searchReview($conditions, $orderBys, $start, $limit, $columns = []);

    public function countRatingByTargetTypeAndTargetId($targetType, $targetId);

    public function getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);
}
