<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Form\ReviewType;

class CourseReviewController extends BaseController
{

    public function listAction(Request $request, $id)
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

        return $this->render('TopxiaWebBundle:CourseReview:list.html.twig', array(
            'course' => $course,
            'reviews' => $reviews,
            'users' => $users,
            'isModal' => $isModal,
            'paginator' => $paginator
        ));
    }

    public function createAction(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($id);
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

	public function latestBlockAction($course)
	{
        $reviews = $this->getReviewService()->findCourseReviews($course['id'], 0, 10);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
    	return $this->render('TopxiaWebBundle:CourseReview:latest-block.html.twig', array(
    		'course' => $course,
            'reviews' => $reviews,
            'users' => $users,
		));

	}

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

}