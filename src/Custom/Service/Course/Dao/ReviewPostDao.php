<?php 
namespace Custom\Service\Course\Dao;
interface ReviewPostDao
{
	const TABLENAME = 'course_review_post';

	public function getReviewPost($id);
	
	public function addReviewPost($reviewPost);

	public function findReviewPostsByReviewIds(array $reviewIds);

	public function deleteReviewPost($id);

	public function updateReviewPost($id,$fields);
}