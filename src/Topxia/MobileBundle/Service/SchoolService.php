<?php
namespace Topxia\MobileBundle\Service;

interface SchoolService
{
	public function getSchoolSite();
	public function getSchoolSiteByQrCode();
	public function getSchoolBanner();
	public function getSchoolAnnouncement();
	public function getRecommendCourses();
}