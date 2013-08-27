<?php
namespace Topxia\Service\Course;

interface ReviewService
{
	public function getReview($id);

	public function findCourseReviews($courseId, $start, $limit);

	public function getCourseReviewCount($courseId);
	
	public function getUserCourseReview($userId, $courseId);

	public function searchReviews($conditions, $sort = 'latest', $start, $limit);

	public function searchReviewsCount($conditions);

	public function saveReview($fields);

	public function deleteReview($id);

}