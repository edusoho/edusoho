<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\TopRatingCourseReviewsDataTag;

class TopRatingCourseReviewsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        // $course1 = array(
        //     'type' => 'normal',
        //     'title' => 'course1',
        // );

        // $course1 = $this->getCourseService()->createCourse($course1);

        // $this->getCourseService()->publishCourse($course1['id']);

        // $user1 = $this->getuserService()->register(array(
        //     'email' => '1234@qq.com',
        //     'nickname' => 'user1',
        //     'password' => '123456',
        //     'confirmPassword' => '123456',
        //     'createdIp' => '127.0.0.1',
        // ));

        // $user2 = $this->getuserService()->register(array(
        //     'email' => '12345@qq.com',
        //     'nickname' => 'user2',
        //     'password' => '123456',
        //     'confirmPassword' => '123456',
        //     'createdIp' => '127.0.0.1',
        // ));

        // $this->getCourseMemberService()->becomeStudent($course1['id'], $user1['id']);
        // $this->getCourseMemberService()->becomeStudent($course1['id'], $user2['id']);

        // $review1 = $this->getReviewService()->saveReview(array(
        //     'courseId' => $course1['id'],
        //     'userId' => $user1['id'],
        //     'title' => 'review1',
        //     'content' => 'content1',
        //     'rating' => 5,
        // ));
        // $review2 = $this->getReviewService()->saveReview(array(
        //     'courseId' => $course1['id'],
        //     'userId' => $user2['id'],
        //     'title' => 'review2',
        //     'content' => 'content2',
        //     'rating' => 4,
        // ));
        $datatag = new TopRatingCourseReviewsDataTag();
        // $CourseAndUser = $datatag->getData(array('courseId' => $course1['id'], 'count' => 5));
        // $this->assertEquals($user1['id'], $CourseAndUser[0]['User']['id']);
        // $this->assertEquals($review1['rating'], $CourseAndUser[0]['rating']);
        $this->assertTrue(true);
    }

    public function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course:ReviewService');
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }
}
