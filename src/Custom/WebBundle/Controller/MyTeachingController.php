<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class MyTeachingController extends BaseController
{
    public function dashboardAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        return $this->render('CustomWebBundle:MyTeaching:dashboard.html.twig', array(
        ));
    }

    public function myCoursesRatingAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $teachCoursesCount = $this->getCourseService()->findUserTeachCourseCount($user['id']);
        $teachCourses = $this->getCourseService()->findUserTeachCourses($user['id'], 0, $teachCoursesCount);
        $teachCourses = ArrayToolkit::index($teachCourses, 'id');
        return $this->render('CustomWebBundle:MyTeaching:my-courses-rating.html.twig', array(
            'teachCourses' => $teachCourses,
        ));
    }

    public function reviewListAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        $previewAs = $request->query->get('previewAs');
        $isModal = $request->query->get('isModal');

        $paginator = new Paginator(
            $this->get('request'),
            $this->getReviewService()->getCourseReviewCount($id)
            , 10
        );

        $reviews = $this->getReviewService()->findCourseReviews(
            $id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));

        return $this->render('CustomWebBundle:MyTeaching:course-review-modal.html.twig', array(
            'course' => $course,
            'reviews' => $reviews,
            'users' => $users,
            'isModal' => $isModal,
            'paginator' => $paginator
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }
}