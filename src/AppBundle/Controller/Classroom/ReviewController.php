<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Review\Service\ReviewService;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends BaseController
{
    public function listAction($id)
    {
        $classroom = $this->getClassroomService()->getClassroom($id);

        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($id);

        $user = $this->getCurrentUser();

        $classroomSetting = $this->setting('classroom', []);
        $classroomName = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';

        $member = $user['id'] ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomName}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }

        $conditions = [
            'targetType' => 'classroom',
            'targetId' => $id,
            'parentId' => 0,
        ];

        $reviewsNum = $this->getReviewService()->countReviews($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $reviewsNum,
            20
        );

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $reviewUserIds = ArrayToolkit::column($reviews, 'userId');
        $reviewUsers = $this->getUserService()->findUsersByIds($reviewUserIds);

        $classroom = $this->getClassroomService()->getClassroom($id);
        $review = $this->getReviewService()->getByUserIdAndTargetTypeAndTargetId($user['id'], 'classroom', $classroom['id']);
        $layout = 'classroom/layout.html.twig';

        if ($member && !$member['locked']) {
            $layout = 'classroom/join-layout.html.twig';
        }

        if (!$classroom) {
            $classroomDescription = [];
        } else {
            $classroomDescription = $classroom['about'];
            $classroomDescription = strip_tags($classroomDescription, '');
            $classroomDescription = preg_replace('/ /', '', $classroomDescription);
        }

        return $this->render('classroom/review/list.html.twig', [
            'classroom' => $classroom,
            'courses' => $courses,
            'paginator' => $paginator,
            'reviewsNum' => $reviewsNum,
            'reviews' => $reviews,
            'userReview' => $review,
            'users' => $reviewUsers,
            'member' => $member,
            'layout' => $layout,
            'classroomDescription' => $classroomDescription,
            'canReview' => $this->isClassroomMember($classroom, $user['id']),
            'targetType' => 'classroom',
        ]);
    }

    public function createAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $fields = $request->request->all();

        $fields['userId'] = $user['id'];
        $fields['classroomId'] = $id;

        $this->getReviewService()->saveReview($fields);
        $response = ['code' => 'success', 'message' => ''];

        return $this->createJsonResponse($response);
    }

    public function postAction(Request $request, $id, $reviewId)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $postNum = $this->getReviewService()->searchReviewCount(['parentId' => $reviewId]);

        if ($postNum >= 5) {
            return $this->createJsonResponse(['error' => '回复数量已达5条上限，不能再回复']);
        }

        $user = $this->getCurrentUser();

        $fields = $request->request->all();
        $fields['userId'] = $user['id'];
        $fields['classroomId'] = $classroom['id'];
        $fields['rating'] = 1;
        $fields['parentId'] = $reviewId;

        $post = $this->getReviewService()->saveReview($fields);

        return $this->render('review/widget/subpost-item.html.twig', [
            'post' => $post,
            'author' => $this->getCurrentUser(),
            'canAccess' => true,
            'targetType' => 'classroom',
        ]);
    }

    public function deleteAction($reviewId)
    {
        $this->getReviewService()->deleteReview($reviewId);

        return $this->createJsonResponse(true);
    }

    protected function isClassroomMember($classroom, $userId)
    {
        if ($classroom['id']) {
            $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $userId);
            if (!empty($member) && array_intersect(['student', 'teacher', 'headTeacher', 'assistant'], $member['role'])) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ReviewService
     */
    private function getReviewService()
    {
        return $this->createService('Review:ReviewService');
    }
}
