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
        if($course['parentId']){
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);

            $classroomSetting = $this->setting('classroom',array());
            $classroomName    = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';

            if(!$this->getClassroomService()->canLookClassroom($classroom['classroomId'])){ 
                return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomName}，如有需要请联系客服",'',3,$this->generateUrl('homepage'));
            }
        }
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

        return $this->render('TopxiaWebBundle:Course:reviews.html.twig', array(
            'course' => $course,
            'member' => $member,
            'reviewSaveUrl' => $this->generateUrl('course_review_create', array('id' => $course['id'])),
            'userReview' => $userReview,
            'reviews' => $reviews,
            'users' => $users,
            'paginator' => $paginator
        ));
    }

    public function createAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
 
        $fields = $request->request->all();
        $fields['userId']= $user['id'];
        $fields['courseId']= $id;
        $this->getReviewService()->saveReview($fields);

        return $this->createJsonResponse(true);
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }
    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
   
}