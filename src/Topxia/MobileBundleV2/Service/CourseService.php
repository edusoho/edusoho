<?php
namespace Topxia\MobileBundleV2\Service;

interface CourseService
{
	public function getVersion();
	public function getCourses();
	public function getLearningCourse();
	public function searchCourse();
	public function getCourse();
}