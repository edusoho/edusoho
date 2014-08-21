<?php
namespace Topxia\MobileBundleV2\Service;

interface SchoolService
{
	public function getSchoolSite();
	public function getSchoolSiteByQrCode();
	public function getSchoolBanner();
	public function getSchoolAnnouncement();
	public function getRecommendCourses();
	public function getWeekRecommendCourses();
	public function getUserterms();
}