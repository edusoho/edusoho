<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Review\Service\ReviewService;
use Symfony\Component\HttpFoundation\Request;

class CourseReviewController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        if (empty($conditions['rating'])) {
            unset($conditions['rating']);
        }

        if (!empty($conditions['courseTitle'])) {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($conditions['courseTitle']);
            $courses = $this->getCourseService()->findCoursesByCourseSetIds(ArrayToolkit::column($courseSets, 'id'));

            $conditions['targetIds'] = ArrayToolkit::column($courses, 'id');
            unset($conditions['courseTitle']);
            $conditions['targetIds'] = $conditions['targetIds'] ?: [-1];
        }

        if (!empty($conditions['author'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['author']);
            unset($conditions['author']);
            $conditions['userId'] = $user['id'] ? $user['id'] : -1;
        }

        $conditions['parentId'] = 0;
        $conditions['targetType'] = 'course';

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

        return $this->render('admin/course-review/index.html.twig', [
            'reviews' => $reviews,
            'users' => $users,
            'courses' => $courses,
            'courseSets' => $courseSets,
            'paginator' => $paginator,
        ]);
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getReviewService()->deleteReview($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids as $id) {
            $this->getReviewService()->deleteReview($id);
        }

        return $this->createJsonResponse(true);
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Review:ReviewService');
    }
}
