<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
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
            $conditions['courseSetIds'] = ArrayToolkit::column($courseSets, 'id');
            unset($conditions['courseTitle']);
            $conditions['courseSetIds'] = $conditions['courseSetIds'] ?: array(-1);
        }

        $paginator = new Paginator(
            $request,
            $this->getReviewService()->searchReviewsCount($conditions),
            20
        );

        $conditions['parentId'] = 0;

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($reviews, 'courseSetId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($reviews, 'courseId'));

        return $this->render('admin/course-review/index.html.twig', array(
            'reviews' => $reviews,
            'users' => $users,
            'courses' => $courses,
            'courseSets' => $courseSets,
            'paginator' => $paginator,
        ));
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

    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }
}
