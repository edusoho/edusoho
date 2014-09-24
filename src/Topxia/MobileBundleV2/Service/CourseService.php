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

	public function getCourseThreads();

	public function commitCourse();

	/**
	 *	courseId 课程id
	 *	threadId 问答id
	 *	token userToken
	*/
	public function getThread();

	public function getThreadTeacherPost();

	/**
	 *	courseId 课程id
	 *	threadId 问答id
	 *	start 起始索引
	 *	limit 分页
	*/
	public function getThreadPost();

	/**
	 *	courseId
	 *	threadId
	 *	content 内容
	 *	imageCount 图片数量
	 *	image1， image2...
	*/
	public function postThread();
}