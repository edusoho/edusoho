<?php

namespace Biz\Course\Service;

use Biz\System\Annotation\Log;

interface ReviewService
{
    public function getReview($id);

    public function findCourseReviews($courseId, $start, $limit);

    public function getCourseReviewCount($courseId);

    public function getUserCourseReview($userId, $courseId);

    public function searchReviews($conditions, $sort, $start, $limit);

    public function searchReviewsCount($conditions);

    public function saveReview($fields);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="course",action="delete_review")
     */
    public function deleteReview($id);

    public function countRatingByCourseId($courseId);

    public function countRatingByCourseSetId($courseSetId);
}
