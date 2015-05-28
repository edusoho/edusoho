<?php
namespace Topxia\MobileBundleV2\Processor;

interface SchoolProcessor
{
	public function getSchoolSite();
	public function getSchoolSiteByQrCode();
	public function getSchoolBanner();
	public function getSchoolAnnouncement();
	public function getRecommendCourses();
	public function getLatestCourses();
	public function getWeekRecommendCourses();
	public function getUserterms();
	public function getSchoolInfo();

	public function sendSuggestion();

	public function getShradCourseUrl();

	public function getClientVersion();

	public function getDownloadUrl();

	public function getFlashApk();

	public function registDevice();

	public function suggestionLog();

	public function loginSchoolWithSite();

	public function getSchoolApps();
}