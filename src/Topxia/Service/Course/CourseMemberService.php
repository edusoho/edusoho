<?php
namespace Topxia\Service\Course;

interface CourseMemberService
{

    public function becomeStudentAndCreateOrder($userId, $courseId, $data);

}