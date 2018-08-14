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
     * @Log(level="info",module="course",action="delete_review",message="删除评价",targetType="course_review",format="{'before':{ 'className':'Course:ReviewService','funcName':'getReview','param':['id']}}")
     */
    public function deleteReview($id);

    public function countRatingByCourseId($courseId);

    public function countRatingByCourseSetId($courseSetId);
}
