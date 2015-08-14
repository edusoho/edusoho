<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Form\ReviewType;
use Topxia\WebBundle\Controller\CourseReviewController as CourseReviewBaseController;

class CourseReviewController extends CourseReviewBaseController
{

    public function indexAction(Request $request, $id)
    {
        list($course, $member) = $this->buildCourseLayoutData($request, $id);

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

        $user = $this->getCurrentUser();
        $userReview = $user->isLogin() ? $this->getReviewService()->getUserCourseReview($user['id'], $course['id']) : null;

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));

        return $this->render('CustomWebBundle:CourseReview:index.html.twig', array(
            'course' => $course,
            'member' => $member,
            'reviewSaveUrl' => $this->generateUrl('course_review_create', array('id' => $course['id'])),
            'userReview' => $userReview,
            'reviews' => $reviews,
            'users' => $users,
            'paginator' => $paginator
        ));
    }
}