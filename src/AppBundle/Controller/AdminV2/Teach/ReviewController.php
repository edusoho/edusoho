<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Classroom\Service\ClassroomReviewService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ReviewService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends BaseController
{
    //课程评价
    public function courseReviewListAction(Request $request)
    {
        $conditions = $request->query->all();

        if (empty($conditions['rating'])) {
            unset($conditions['rating']);
        }

        if (!empty($conditions['courseTitle'])) {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($conditions['courseTitle']);
            $conditions['courseSetIds'] = ArrayToolkit::column($courseSets, 'id');
            unset($conditions['courseTitle']);
            $conditions['courseSetIds'] = $conditions['courseSetIds'] ?: array(-1);
        }

        $paginator = new Paginator(
            $request,
            $this->getCourseReviewService()->searchReviewsCount($conditions),
            20
        );

        $conditions['parentId'] = 0;

        $reviews = $this->getCourseReviewService()->searchReviews(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($reviews, 'courseSetId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($reviews, 'courseId'));

        return $this->render('admin-v2/teach/review/course-review.html.twig', array(
            'reviews' => $reviews,
            'users' => $users,
            'courses' => $courses,
            'courseSets' => $courseSets,
            'paginator' => $paginator,
        ));
    }

    public function deleteCourseReviewAction(Request $request, $id)
    {
        $this->getCourseReviewService()->deleteReview($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteCourseReviewAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids as $id) {
            $this->getCourseReviewService()->deleteReview($id);
        }

        return $this->createJsonResponse(true);
    }

    //班级评价
    public function classroomReviewListAction(Request $request)
    {
        $conditions = $request->query->all();

        if (empty($conditions['rating'])) {
            unset($conditions['rating']);
        }

        if (!empty($conditions['classroomTitle'])) {
            $classrooms = $this->getClassroomService()->findClassroomsByLikeTitle(trim($conditions['classroomTitle']));
            $conditions['classroomIds'] = ArrayToolkit::column($classrooms, 'id');
            if (0 == count($conditions['classroomIds'])) {
                return $this->render('admin-v2/teach/review/classroom-review.html.twig', array(
                    'reviews' => array(),
                    'users' => array(),
                    'classrooms' => array(),
                    'paginator' => new Paginator($request, 0, 20),
                ));
            }
        }

        $conditions['parentId'] = 0;
        $paginator = new Paginator(
            $request,
            $this->getClassroomReviewService()->searchReviewCount($conditions),
            20
        );

        $reviews = $this->getClassroomReviewService()->searchReviews(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($reviews, 'classroomId'));

        return $this->render('admin-v2/teach/review/classroom-review.html.twig', array(
            'reviews' => $reviews,
            'users' => $users,
            'classrooms' => $classrooms,
            'paginator' => $paginator,
        ));
    }

    public function deleteClassroomReviewAction(Request $request, $id)
    {
        $this->getClassroomReviewService()->deleteReview($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteClassroomReviewAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids as $id) {
            $this->getClassroomReviewService()->deleteReview($id);
        }

        return $this->createJsonResponse(true);
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return ClassroomReviewService
     */
    protected function getClassroomReviewService()
    {
        return $this->createService('Classroom:ClassroomReviewService');
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
    protected function getCourseReviewService()
    {
        return $this->createService('Course:ReviewService');
    }
}
