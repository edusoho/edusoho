<?php
namespace Classroom\ClassroomBundle\Controller\Classroom;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class ReviewController extends BaseController
{
    public function listAction(Request $request, $id)
    {
        $classroom = $this->getClassroomService()->getClassroom($id);

        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($id);

        $user = $this->getCurrentUser();

        $classroomSetting = $this->setting('classroom', array());
        $classroomName    = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;
        if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomName}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }

        $conditions = array(
            'classroomId' => $id
        );

        $reviewsNum = $this->getClassroomReviewService()->searchReviewCount($conditions);
        $paginator  = new Paginator(
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
        $reviewUsers   = $this->getUserService()->findUsersByIds($reviewUserIds);

        $classroom = $this->getClassroomService()->getClassroom($id);
        $review    = $this->getClassroomReviewService()->getUserClassroomReview($user['id'], $classroom['id']);
        $layout    = 'ClassroomBundle:Classroom:layout.html.twig';

        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }

        if (!$classroom) {
            $classroomDescription = array();
        } else {
            $classroomDescription = $classroom['about'];
            $classroomDescription = strip_tags($classroomDescription, '');
            $classroomDescription = preg_replace("/ /", "", $classroomDescription);
        }

        return $this->render("ClassroomBundle:Classroom\Review:list.html.twig", array(
            'classroom'            => $classroom,
            'courses'              => $courses,
            'paginator'            => $paginator,
            'reviewsNum'           => $reviewsNum,
            'reviews'              => $reviews,
            'userReview'           => $review,
            'reviewSaveUrl'        => $this->generateUrl('classroom_review_create', array('id' => $id)),
            'users'                => $reviewUsers,
            'member'               => $member,
            'layout'               => $layout,
            'classroomDescription' => $classroomDescription,
            'canReview'            => $this->isClassroomMember($classroom, $user['id'])
        ));
    }

    public function createAction(Request $request, $id)
    {
        $user                  = $this->getCurrentUser();
        $fields                = $request->request->all();
        $fields['userId']      = $user['id'];
        $fields['classroomId'] = $id;
        $this->getClassroomReviewService()->saveReview($fields);

        return $this->createJsonResponse(true);
    }

    protected function isClassroomMember($classroom, $userId)
    {
        if ($classroom['id']) {
            $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $userId);
            if (!empty($member) && array_intersect(array('student', 'teacher', 'headTeacher', 'assistant'), $member['role'])) {
                return 1;
            }
        }

        return 0;
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
