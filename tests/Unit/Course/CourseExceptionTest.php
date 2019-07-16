<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Course\CourseException;

class CourseExceptionTest extends BaseTestCase
{
    public function testException()
    {
        $exceptionModual = CourseException::EXCEPTION_MODUAL;
        $notFoundCourse = CourseException::NOTFOUND_COURSE;
        $forbiddenTaskCourse = CourseException::FORBIDDEN_TAKE_COURSE;
        $learnmodeInvalid = CourseException::LEARNMODE_INVALID;
        $courseTypeInvalid = CourseException::COURSETYPE_INVALID;
        $courseNumLimit = CourseException::COURSE_NUM_LIMIT;
        $forbiddenDeletePublish = CourseException::FORBIDDEN_DELETE_PUBLISHED;
        $subCourseExist = CourseException::SUB_COURSE_EXIST;
        $courseNumRequired = CourseException::COURSE_NUM_REQUIRED;
        $unpubilshedCourse = CourseException::UNPUBLISHED_COURSE;
        $notFoundChapter = CourseException::NOTFOUND_CHAPTER;
        $chainNotRegistered = CourseException::CHAIN_NOT_REGISTERED;
        $expirymodeInvalid = CourseException::EXPIRYMODE_INVALID;
        $expiryDaysRequired = CourseException::EXPIRYDAYS_REQUIRED;
        $expirystartdateRequired = CourseException::EXPIRYSTARTDATE_REQUIRED;
        $expiryendDateRequired = CourseException::EXPIRYENDDATE_REQUIRED;
        $expiryDateSetInvalid = CourseException::EXPIRY_DATE_SET_INVALID;
        $notMatchCourseSet = CourseException::NOT_MATCH_COURSESET;
        $chapterTypeInvalid = CourseException::CHAPTERTYPE_INVALID;
        $forbiddenManegeCourse = CourseException::FORBIDDEN_MANAGE_COURSE;
        $closedCourse = CourseException::CLOSED_COURSE;
        $unBuyableCourse = CourseException::UNBUYABLE_COURSE;
        $expiredCourse = CourseException::EXPIRED_COURSE;
        $buyExpired = CourseException::BUY_EXPIRED;
        $reachMaxStudent = CourseException::REACH_MAX_STUDENT;
        $unArrive = CourseException::UN_ARRIVE;
        $searchOrderClosed = CourseException::SEARCH_ORDER_CLOSED;
        $forbiddenLearnCourse = CourseException::FORBIDDEN_LEARN_COURSE;

        $this->assertEquals(16, $exceptionModual);
        $this->assertEquals(4041601, $notFoundCourse);
        $this->assertEquals(4031602, $forbiddenTaskCourse);
        $this->assertEquals(5001603, $learnmodeInvalid);
        $this->assertEquals(5001604, $courseTypeInvalid);
        $this->assertEquals(4031605, $courseNumLimit);
        $this->assertEquals(4031606, $forbiddenDeletePublish);
        $this->assertEquals(5001607, $subCourseExist);
        $this->assertEquals(4031608, $courseNumRequired);
        $this->assertEquals(4031609, $unpubilshedCourse);
        $this->assertEquals(4041610, $notFoundChapter);
        $this->assertEquals(5001611, $chainNotRegistered);
        $this->assertEquals(5001612, $expirymodeInvalid);
        $this->assertEquals(5001613, $expiryDaysRequired);
        $this->assertEquals(5001614, $expirystartdateRequired);
        $this->assertEquals(5001615, $expiryendDateRequired);
        $this->assertEquals(5001616, $expiryDateSetInvalid);
        $this->assertEquals(5001617, $notMatchCourseSet);
        $this->assertEquals(5001618, $chapterTypeInvalid);
        $this->assertEquals(4031619, $forbiddenManegeCourse);
        $this->assertEquals(5001620, $closedCourse);
        $this->assertEquals(4031621, $unBuyableCourse);
        $this->assertEquals(5001622, $expiredCourse);
        $this->assertEquals(5001623, $buyExpired);
        $this->assertEquals(4031624, $reachMaxStudent);
        $this->assertEquals(5001625, $unArrive);
        $this->assertEquals(4031626, $searchOrderClosed);
        $this->assertEquals(4031627, $forbiddenLearnCourse);
    }
}
