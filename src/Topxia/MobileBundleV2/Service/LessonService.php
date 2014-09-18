<?php
namespace Topxia\MobileBundleV2\Service;

interface LessonService
{
	public function getCourseLessons();
	public function getLesson();
	public function getLessonMaterial();

	/*
	* 	courseId
	*	lessonId
	*	token
	*/
	public function learnLesson();

	/*
	* 	courseId
	*	lessonId
	*	token
	*/
	public function unLearnLesson();
}