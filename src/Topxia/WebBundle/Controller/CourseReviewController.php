<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Form\ReviewType;

class CourseReviewController extends CourseBaseController
{

    public function listAction(Request $request, $id)
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

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));

        return $this->render('TopxiaWebBundle:CourseReview:list.html.twig', array(
            'course' => $course,
            'member' => $member,
            'reviews' => $reviews,
            'users' => $users,
            'paginator' => $paginator
        ));
    }

    public function createAction(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);
        $review = $this->getReviewService()->getUserCourseReview($currentUser['id'], $course['id']);
        $form = $this->createForm(new ReviewType(), $review ? : array());

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $fields = $form->getData();
                $fields['rating'] = $fields['rating'];
                $fields['userId']= $currentUser['id'];
                $fields['courseId']= $id;
                $this->getReviewService()->saveReview($fields);
                return $this->createJsonResponse(true);
            }
        }

        return $this->render('TopxiaWebBundle:CourseReview:write-modal.html.twig', array(
            'form' => $form->createView(),
            'course' => $course,
            'review' => $review,
        ));
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

}