<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ReviewController extends BaseController {

    public function indexAction (Request $request)
    {   
        $conditions = $request->query->all();
        $paginator = new Paginator(
            $request,
            $this->getReviewService()->searchReviewsCount($conditions),
            20
        );

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        ); 

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($reviews, 'courseId'));

        return $this->render('TopxiaAdminBundle:Review:index.html.twig',array(
            'reviews' => $reviews,
            'users'=>$users,
            'courses'=>$courses,
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
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }


}