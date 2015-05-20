<?php
namespace Classroom\ClassroomBundle\Controller\Classroom;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\Paginator;
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

        $conditions = array(
            'classroomId' => $id,
        );

        $reviewsNum = $this->getClassroomReviewService()->searchReviewCount($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $reviewsNum, 20
        );

        $reviews = $this->getClassroomReviewService()->searchReviews(
            $conditions, array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $reviewUsers = array();
        foreach ($reviews as $review) {
            $reviewUsers[$review['id']] =  $this->getUserService()->getUser($review['userId']);
        }

        $classroom = $this->getClassroomService()->getClassroom($id);
        $review = $this->getClassroomReviewService()->getUserClassroomReview($user['id'], $classroom['id']);
        $form = $this->createForm(new ClassroomReviewType(), $review ?: array());

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $fields = $form->getData();
                $fields['rating'] = $fields['rating'];
                $fields['userId'] = $user['id'];
                $fields['classroomId'] = $id;
                $this->getClassroomReviewService()->saveReview($fields);

                return $this->createJsonResponse(true);
            }
        }

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
            $form =  $form->createView();
        }

        return $this->render("ClassroomBundle:Classroom\Review:list.html.twig", array(
            'classroom' => $classroom,
            'courses' => $courses,
            'paginator' => $paginator,
            'reviewsNum' => $reviewsNum,
            'reviews' => $reviews,
            'review' => $review,
            'users' => $reviewUsers,
            'member' => $member,
            'form' => $form,
            'layout' => $layout
        ));
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
