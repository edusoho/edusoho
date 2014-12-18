<?php 
namespace Custom\Service\Course;

interface ReviewService{
	public function getReviewPost($id);

	public function saveReviewPost($fields);

	public function findReviewPostsByReviewIds(array $reviewIds);

	public function deleteReviewPost($id);

	public function updateReviewPost($id,$fields);
}