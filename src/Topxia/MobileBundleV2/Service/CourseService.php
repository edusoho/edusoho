<?php
namespace Topxia\MobileBundleV2\Service;

interface CourseService
{
	public function getVersion();
	public function getCourses();
	public function getLearningCourse();
	public function getLearnedCourse();
	public function getFavoriteCoruse();
	public function searchCourse();
	public function getCourse();
	public function getReviews();

	public function favoriteCourse();
	public function unFavoriteCourse();
	public function getTeacherCourses();

	public function getCourseNotice();
	public function unLearnCourse();

	public function getCourseTheads();
	public function getThread();
}