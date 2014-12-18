<?php
namespace Custom\WebBundle\Controller;
use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Form\ReviewType;

class CourseReviewController extends BaseController
{

    public function listAction(Request $request, $id)
    {
        return $this->reviewList($request, $id);
    }

    public function createAction(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);
        $review = $this->getReviewService()->getUserCourseReview($currentUser['id'], $course['id']);
        $form = $this->createForm(new ReviewType(), $review ? : array());

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields['userId']= $currentUser['id'];
            $fields['courseId']= $id;
            $this->getReviewService()->saveReview($fields);
            return $this->reviewList($request, $id);
        }

        return $this->render('TopxiaWebBundle:CourseReview:write-modal.html.twig', array(
            'form' => $form->createView(),
            'course' => $course,
            'review' => $review,
        ));
    }

    public function createPostAction(Request $request,$id)
    {
        $user=$this->getCurrentUser();
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);
        if(in_array($user['id'], $course['teacherIds']) || $user->isAdmin()){
            $fields = $request->request->all();
            $fields['userId']= $user['id'];
            $fields['courseId']= $id;
            $this->getReviewService()->saveReviewPost($fields);

            $review=$this->getReviewService()->getReview($fields['reviewId']);
            $reviewPosts=$this->getReviewService()->findReviewPostsByReviewIds(array($fields['reviewId']));
            $postUsers =$this->getUserService()->findUsersByIds(ArrayToolkit::column($reviewPosts, 'userId'));
            $reviewPosts = ArrayToolkit::group($reviewPosts,'reviewId');
            return $this->render('TopxiaWebBundle:CourseReview:post-list.html.twig', array(
                'review' => $review,
                'reviewPosts' => $reviewPosts,
                'postUsers'=>$postUsers
            ));
        }
    }

    private function reviewList(Request $request, $id)
    {
        $user=$this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($id);
        $canPost=0;
        if(in_array($user['id'], $course['teacherIds']) || $user->isAdmin()){
            $canPost=1;
        }
        $paginator = new Paginator(
            $this->get('request'),
            $this->getReviewService()->getCourseReviewCount($id)
            , 5
        );

        $reviews = $this->getReviewService()->findCourseReviews(
            $id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $reviewPosts=$this->getReviewService()->findReviewPostsByReviewIds(ArrayToolkit::column($reviews, 'id'));
        $postUsers =$this->getUserService()->findUsersByIds(ArrayToolkit::column($reviewPosts, 'userId'));
        $reviewPosts = ArrayToolkit::group($reviewPosts,'reviewId');
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        return $this->render('TopxiaWebBundle:CourseReview:list.html.twig', array(
            'course' => $course,
            'reviews' => $reviews,
            'reviewPosts' => $reviewPosts,
            'users' => $users,
            'postUsers'=>$postUsers,
            'paginator' => $paginator,
            'canPost'=>$canPost
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
        return $this->getServiceKernel()->createService('Custom:Course.ReviewService');
    }

}