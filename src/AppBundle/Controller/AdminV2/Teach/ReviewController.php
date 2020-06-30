<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Review\Service\ReviewService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends BaseController
{
    //课程评价
    public function courseReviewListAction(Request $request)
    {
        $conditions = array_merge(['targetType' => 'course'], $request->query->all());

        $conditions = $this->prepareConditions($conditions);

        $paginator = new Paginator(
            $request,
            $this->getReviewService()->countReviews($conditions),
            20
        );

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($reviews, 'targetId'));
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($courses, 'courseSetId'));

        return $this->render('admin-v2/teach/review/course-review.html.twig', [
            'reviews' => $reviews,
            'users' => $users,
            'courses' => $courses,
            'courseSets' => $courseSets,
            'paginator' => $paginator,
        ]);
    }

    public function deleteCourseReviewAction(Request $request, $id)
    {
        $this->getReviewService()->deleteReview($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteCourseReviewAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids as $id) {
            $this->getReviewService()->deleteReview($id);
        }

        return $this->createJsonResponse(true);
    }

    //班级评价
    public function classroomReviewListAction(Request $request)
    {
        $conditions = array_merge(['targetType' => 'classroom'], $request->query->all());

        $conditions = $this->prepareConditions($conditions);

        $paginator = new Paginator(
            $request,
            $this->getReviewService()->countReviews($conditions),
            20
        );

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($reviews, 'targetId'));

        return $this->render('admin-v2/teach/review/classroom-review.html.twig', [
            'reviews' => $reviews,
            'users' => $users,
            'classrooms' => $classrooms,
            'paginator' => $paginator,
        ]);
    }

    public function deleteClassroomReviewAction(Request $request, $id)
    {
        $this->getReviewService()->deleteReview($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteClassroomReviewAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids as $id) {
            $this->getReviewService()->deleteReview($id);
        }

        return $this->createJsonResponse(true);
    }

    protected function prepareConditions($conditions)
    {
        if (empty($conditions['rating'])) {
            unset($conditions['rating']);
        }

        if (!empty($conditions['courseTitle']) && 'course' == $conditions['targetType']) {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($conditions['courseTitle']);
            $courses = $this->getCourseService()->findCoursesByCourseSetIds(ArrayToolkit::column($courseSets, 'id'));

            $conditions['targetIds'] = ArrayToolkit::column($courses, 'id');
            unset($conditions['courseTitle']);
            $conditions['targetIds'] = $conditions['targetIds'] ?: [-1];
        }

        if (!empty($conditions['classroomTitle']) && 'classroom' == $conditions['targetType']) {
            $classrooms = $this->getClassroomService()->findClassroomsByLikeTitle(trim($conditions['classroomTitle']));
            $conditions['targetIds'] = ArrayToolkit::column($classrooms, 'id');
            unset($conditions['classroomTitle']);
            $conditions['targetIds'] = $conditions['targetIds'] ?: [-1];
        }

        if (!empty($conditions['author'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['author']);
            unset($conditions['author']);
            $conditions['userId'] = $user['id'] ? $user['id'] : -1;
        }

        $conditions['parentId'] = 0;

        return $conditions;
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
        return $this->createService('Review:ReviewService');
    }
}
