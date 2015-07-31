<?php
namespace Topxia\MobileBundleV2\Processor;

interface ClassRoomProcessor
{
	public function getClassRooms();

	public function myClassRooms();

	public function getClassRoom();

	public function getClassRoomCourses();

	public function getLatestClassrooms();

	public function getRecommendClassRooms();

	public function getClassRoomMember();

	public function learnByVip();

	public function unLearn();
}