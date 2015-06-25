<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\ReviewService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class ReviewServiceTest extends BaseTestCase
{
    /**
     * @group current
     */
    public function testSaveReview()
    {
        $course = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($course);
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $reviewInfo = array(
            'title'=>'title',
            'content'=>'content',
            'rating'=>$createdCourse['rating'],
            'userId'=>$registeredUser['id'],
            'courseId'=>$createdCourse['id']
            );
        $savedReview = $this->getReviewService()->saveReview($reviewInfo);
        $this->assertNotNull($savedReview);
    }

    /**
     * @group review
     */
    public function testSaveReviewTwice()
    {
        $course = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($course);
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $reviewInfo1 = array(
            'title'=>'title',
            'content'=>'content',
            'rating'=>$createdCourse['rating'],
            'userId'=>$registeredUser['id'],
            'courseId'=>$createdCourse['id']
            );
        $reviewInfo2 = array(
            'title'=>'title',
            'content'=>'content',
            'rating'=>$createdCourse['rating'],
            'userId'=>$registeredUser['id'],
            'courseId'=>$createdCourse['id']
            );
        $savedReview = $this->getReviewService()->saveReview($reviewInfo1);
        $savedReview = $this->getReviewService()->saveReview($reviewInfo2);
        $this->assertNotNull($savedReview);
    }

    /**
     * @group review
     */
    public function testGetView()
    {
        $course = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($course);
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $reviewInfo1 = array(
            'title'=>'title',
            'content'=>'content',
            'rating'=>$createdCourse['rating'],
            'userId'=>$registeredUser['id'],
            'courseId'=>$createdCourse['id']
            );
        $reviewInfo2 = array(
            'title'=>'title',
            'content'=>'content',
            'rating'=>$createdCourse['rating'],
            'userId'=>$registeredUser['id'],
            'courseId'=>$createdCourse['id']
            );
        $savedReview = $this->getReviewService()->saveReview($reviewInfo1);
        $getedReview = $this->getReviewService()->getReview($savedReview['id']);
        $this->assertEquals($savedReview, $getedReview);
    }

    /**
     * @group review
     */
    public function testGetUserCourseReview()
    {
        $course = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($course);
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $reviewInfo1 = array(
            'title'=>'title',
            'content'=>'content',
            'rating'=>$createdCourse['rating'],
            'userId'=>$registeredUser['id'],
            'courseId'=>$createdCourse['id']
            );
        $savedReview = $this->getReviewService()->saveReview($reviewInfo1);
        $result = $this->getReviewService()->getUserCourseReview($registeredUser['id'], $createdCourse['id']);
        $this->assertEquals($savedReview['id'], $result['id']);
    }

    /**
     * @group current
     */
    public function testSearchReviewsCount()
    {
        $course = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($course);
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $reviewInfo1 = array(
            'title'=>'title',
            'content'=>'content',
            'rating'=>$createdCourse['rating'],
            'userId'=>$registeredUser['id'],
            'courseId'=>$createdCourse['id']
            );
        $savedReview = $this->getReviewService()->saveReview($reviewInfo1);
        $resultId = $this->getReviewService()->searchReviewsCount(array(
            'keywordType'=>'title',
            'keyword'=>'title'
            ));
        $this->assertEquals($savedReview['id'], $resultId);
    }

    public function testSearchReviews()
    {
        $course = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($course);
        $userInfo1 = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser1 = $this->getUserService()->register($userInfo1);

        $userInfo2 = array(
            'nickname'=>'tesnickname', 
            'password'=> 'test_password',
            'email'=>'test_another_email@email.com'
        );
        $registeredUser2 = $this->getUserService()->register($userInfo2);

        $reviewInfo1 = array(
            'title'=>'title1',
            'content'=>'content1',
            'rating'=>$createdCourse['rating'],
            'userId'=>$registeredUser1['id'],
            'courseId'=>$createdCourse['id']
            );
         $reviewInfo2 = array(
            'title'=>'title2',
            'content'=>'content2',
            'rating'=>$createdCourse['rating'],
            'userId'=>$registeredUser2['id'],
            'courseId'=>$createdCourse['id']
            );
        $savedReview1 = $this->getReviewService()->saveReview($reviewInfo1);
        $savedReview2 = $this->getReviewService()->saveReview($reviewInfo2);
        $searchedReviews = $this->getReviewService()->searchReviews(
            array('keywordType'=>'title','keyword'=>'title'),
            'latest',
            0,30);
        $this->assertContains($savedReview2, $searchedReviews);

        $searchedReviews = $this->getReviewService()->searchReviews(
            array('keywordType'=>'content','keyword'=>'content'),
            'latest',
            0,30);
        $this->assertContains($savedReview2, $searchedReviews);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}