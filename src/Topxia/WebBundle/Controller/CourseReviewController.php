<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class CourseReviewController extends CourseBaseController
{
    public function listAction(Request $request, $id)
    {
        list($course, $member) = $this->buildCourseLayoutData($request, $id);
        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);

            $classroomSetting = $this->setting('classroom', array());
            $classroomName    = isset($classroomSetting['name']) ? $classroomSetting['name'] : $this->getServiceKernel()->trans('班级');

            if (!$this->getClassroomService()->canLookClassroom($classroom['classroomId'])) {
                return $this->createMessageResponse('info', $this->getServiceKernel()->trans('非常抱歉，您无权限访问该%classroomName%，如有需要请联系客服', array('%classroomName%' => $classroomName)), '', 3, $this->generateUrl('homepage'));
            }
        }

        $conditions = array(
            'courseId' => $id,
            'parentId' => 0
        );
        $paginator = new Paginator(
            $this->get('request'),
            $this->getReviewService()->searchReviewsCount($conditions)
            , 10
        );

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $user       = $this->getCurrentUser();
        $userReview = $user->isLogin() ? $this->getReviewService()->getUserCourseReview($user['id'], $course['id']) : null;

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));

        return $this->render('TopxiaWebBundle:Course:reviews.html.twig', array(
            'course'        => $course,
            'member'        => $member,
            'reviewSaveUrl' => $this->generateUrl('course_review_create', array('id' => $course['id'])),
            'userReview'    => $userReview,
            'reviews'       => $reviews,
            'users'         => $users,
            'paginator'     => $paginator
        ));
    }

    public function createAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        $fields             = $request->request->all();
        $fields['userId']   = $user['id'];
        $fields['courseId'] = $id;
        $this->getReviewService()->saveReview($fields);

        return $this->createJsonResponse(true);
    }

    public function postAction(Request $request, $id, $reviewId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $postNum = $this->getReviewService()->searchReviewsCount(array('parentId' => $reviewId));

        if ($postNum >= 5) {
            return $this->createJsonResponse(array('error' => $this->trans('回复数量已达5条上限，不能再回复')));
        }

        $user = $this->getCurrentUser();

        $fields             = $request->request->all();
        $fields['userId']   = $user['id'];
        $fields['courseId'] = $course['id'];
        $fields['rating']   = 1;
        $fields['parentId'] = $reviewId;

        $post = $this->getReviewService()->saveReview($fields);

        return $this->render("TopxiaWebBundle:Review/Widget:subpost-item.html.twig", array(
            'post'      => $post,
            'author'    => $this->getCurrentUser(),
            'canAccess' => true
        ));
    }

    public function deleteAction(Request $request, $reviewId)
    {
        $this->getReviewService()->deleteReview($reviewId);
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
