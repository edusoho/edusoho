<?php

namespace AppBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\HttpFoundation\Request;

class CourseSetController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        list($courseSet, $course) = $this->getCourseSetAndCourse($request, $id);
        return $this->render('courseset/overview.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    public function noteListAction(Request $request, $id)
    {
    }

    public function reviewListAction(Request $request, $id)
    {
        list($courseSet, $course) = $this->getCourseSetAndCourse($request, $id);
        list($course, $member)    = $this->getCourseService()->tryTakeCourse($course['id']);

        $courseId = $request->query->get('courseId', 0);

        $conditions = array(
            'courseSetId' => $courseSet['id'],
            'parentId'    => 0
        );

        if ($courseId > 0) {
            $conditions['courseId'] = $courseId;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getReviewService()->searchReviewsCount($conditions),
            20
        );

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $user       = $this->getCurrentUser();
        $userReview = $this->getReviewService()->getUserCourseReview($user['id'], $course['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));

        return $this->render('courseset/review/list.html.twig', array(
            'courseSet'  => $courseSet,
            'course'     => $course,
            'reviews'    => $reviews,
            'userReview' => $userReview,
            'users'      => $users,
            'member'     => $member
        ));
    }

    protected function getCourseSetAndCourse(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);

        $courseId = $request->query->get('courseId', 0);

        if ($courseId > 0) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($id);
        }

        return array($courseSet, $course);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
