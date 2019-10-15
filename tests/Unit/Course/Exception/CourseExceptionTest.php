<?php

namespace Tests\Unit\Course\Exception;

use Biz\BaseTestCase;
use Biz\Course\CourseException;

class CourseExceptionTest extends BaseTestCase
{
    public function testNotFoundCourse()
    {
        $exception = CourseException::NOTFOUND_COURSE();

        $this->assertEquals('exception.course.not_found', $exception->getMessage());
    }

    public function testForbiddenTakeCourse()
    {
        $exception = CourseException::FORBIDDEN_TAKE_COURSE();

        $this->assertEquals('exception.course.forbidden_take_course', $exception->getMessage());
    }

    public function testLearnmodeInvalid()
    {
        $exception = CourseException::LEARNMODE_INVALID();

        $this->assertEquals('exception.course.learnmode_invalid', $exception->getMessage());
    }

    public function testCoursetypeInvalid()
    {
        $exception = CourseException::COURSETYPE_INVALID();

        $this->assertEquals('exception.course.coursetype_invalid', $exception->getMessage());
    }

    public function testCourseNumLimit()
    {
        $exception = CourseException::COURSE_NUM_LIMIT();

        $this->assertEquals('exception.course.count_no_more_than_10', $exception->getMessage());
    }

    public function testForbiddenDeletePublished()
    {
        $exception = CourseException::FORBIDDEN_DELETE_PUBLISHED();

        $this->assertEquals('exception.course.forbidden_delete_published', $exception->getMessage());
    }

    public function testSubCourseExist()
    {
        $exception = CourseException::SUB_COURSE_EXIST();

        $this->assertEquals('exception.course.have_sub_courses', $exception->getMessage());
    }

    public function testCourseNumRequired()
    {
        $exception = CourseException::COURSE_NUM_REQUIRED();

        $this->assertEquals('exception.course.count_no_less_than_1', $exception->getMessage());
    }

    public function testUnpublishedCourse()
    {
        $exception = CourseException::UNPUBLISHED_COURSE();

        $this->assertEquals('exception.course.course_not_published', $exception->getMessage());
    }

    public function testNotFoundChapter()
    {
        $exception = CourseException::NOTFOUND_CHAPTER();

        $this->assertEquals('exception.course.not_found_chapter', $exception->getMessage());
    }

    public function testChainNotRegistered()
    {
        $exception = CourseException::CHAIN_NOT_REGISTERED();

        $this->assertEquals('exception.course.chain_not_registered', $exception->getMessage());
    }

    public function testExpiryModeInvalid()
    {
        $exception = CourseException::EXPIRYMODE_INVALID();

        $this->assertEquals('exception.course.expirymode_invalid', $exception->getMessage());
    }

    public function testExpiryDaysRequired()
    {
        $exception = CourseException::EXPIRYDAYS_REQUIRED();

        $this->assertEquals('exception.course.expirydays_required', $exception->getMessage());
    }

    public function testExpiryStartDateRequired()
    {
        $exception = CourseException::EXPIRYSTARTDATE_REQUIRED();

        $this->assertEquals('exception.course.expirystartdate_required', $exception->getMessage());
    }

    public function testExpiryenddateRequired()
    {
        $exception = CourseException::EXPIRYENDDATE_REQUIRED();

        $this->assertEquals('exception.course.expiryenddate_required', $exception->getMessage());
    }

    public function testExpiryDateSetInvalid()
    {
        $exception = CourseException::EXPIRY_DATE_SET_INVALID();

        $this->assertEquals('exception.course.expirydate_end_later_than_start', $exception->getMessage());
    }

    public function testNotMatachCourseSet()
    {
        $exception = CourseException::NOT_MATCH_COURSESET();

        $this->assertEquals('exception.course.not_match_courseset', $exception->getMessage());
    }

    public function testChapterTypeInvalid()
    {
        $exception = CourseException::CHAPTERTYPE_INVALID();

        $this->assertEquals('exception.course.chapter_type_invalid', $exception->getMessage());
    }

    public function testForbiddenManageCourse()
    {
        $exception = CourseException::FORBIDDEN_MANAGE_COURSE();

        $this->assertEquals('exception.course.forbidden_manage_course', $exception->getMessage());
    }

    public function testClosedCourse()
    {
        $exception = CourseException::CLOSED_COURSE();

        $this->assertEquals('exception.course.closed_course', $exception->getMessage());
    }

    public function testUnbuyableCourse()
    {
        $exception = CourseException::UNBUYABLE_COURSE();

        $this->assertEquals('exception.course.unbuyable_course', $exception->getMessage());
    }

    public function testExpiredCourse()
    {
        $exception = CourseException::EXPIRED_COURSE();

        $this->assertEquals('exception.course.expired_course', $exception->getMessage());
    }

    public function testBuyExpired()
    {
        $exception = CourseException::BUY_EXPIRED();

        $this->assertEquals('exception.course.buy_expired', $exception->getMessage());
    }

    public function testReachMaxStudent()
    {
        $exception = CourseException::REACH_MAX_STUDENT();

        $this->assertEquals('exception.course.reach_max_student_num', $exception->getMessage());
    }

    public function testUnArrive()
    {
        $exception = CourseException::UN_ARRIVE();

        $this->assertEquals('exception.course.not_arrive', $exception->getMessage());
    }

    public function testSreachOrderClosed()
    {
        $exception = CourseException::SEARCH_ORDER_CLOSED();

        $this->assertEquals('exception.course.search_order_closed', $exception->getMessage());
    }

    public function testForbiddenLearnCourse()
    {
        $exception = CourseException::FORBIDDEN_LEARN_COURSE();

        $this->assertEquals('exception.course.forbidden_learn_course', $exception->getMessage());
    }
}
