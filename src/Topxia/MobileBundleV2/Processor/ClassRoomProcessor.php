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

    public function getReviews();

    public function getReviewInfo();

    public function getStudents();

    public function getTeachers();

    public function getTodaySignInfo();

    public function sign();

    public function search();

    public function getClassRoomCoursesAndProgress();
}
