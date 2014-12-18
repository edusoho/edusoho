<?php 
namespace Custom\Service\Course;

interface ReviewService{
	public function saveReviewPost($fields);

	public function findReviewPostsByReviewIds(array $reviewIds);
}