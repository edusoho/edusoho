<?php
namespace Classroom\ClassroomBundle\Controller\Classroom;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Form\ClassroomReviewType;

class ReviewController extends BaseController
{
    public function listAction(Request $request, $id)
    {
        $classroom = $this->getClassroomService()->getClassroom($id);

        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($id);
        $coursesNum = count($courses);

        $user = $this->getCurrentUser();

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if ($classroom['private'] && (!$member || ($member && $member['locked']))) {
            return $this->createMessageResponse('error', '该班级是封闭班级,您无权查看');
        }

        $conditions = array(
            'classroomId' => $id,
        );

        $reviewsNum = $this->getClassroomReviewService()->searchReviewCount($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $reviewsNum,
            20
        );

        $reviews = $this->getClassroomReviewService()->searchReviews(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );


        $reviewUserIds = ArrayToolkit::column($reviews, 'userId');
        $reviewUsers = $this->getUserService()->findUsersByIds($reviewUserIds);

        $classroom = $this->getClassroomService()->getClassroom($id);
        $review = $this->getClassroomReviewService()->getUserClassroomReview($user['id'], $classroom['id']);

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }

        return $this->render("ClassroomBundle:Classroom\Review:list.html.twig", array(
            'classroom' => $classroom,
            'courses' => $courses,
            'paginator' => $paginator,
            'reviewsNum' => $reviewsNum,
            'reviews' => $reviews,
            'userReview' => $review,
            'reviewSaveUrl' => $this->generateUrl('classroom_review_create', array('id' => $id)),
            'users' => $reviewUsers,
            'member' => $member,
            'layout' => $layout,
        ));
    }

    public function createAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $fields = $request->request->all();
        $fields['rating'] = $fields['rating'];
        $fields['userId'] = $user['id'];
        $fields['classroomId'] = $id;
        $this->getClassroomReviewService()->saveReview($fields);

        return $this->createJsonResponse(true);
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getClassroomReviewService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomReviewService');
    }
}
