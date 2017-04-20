<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ClassroomReviewController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        if (empty($conditions['rating'])) {
            unset($conditions['rating']);
        }

        if (!empty($conditions['classroomTitle'])) {
            $classrooms = $this->getClassroomService()->findClassroomsByLikeTitle(trim($conditions['classroomTitle']));
            $conditions['classroomIds'] = ArrayToolkit::column($classrooms, 'id');
            if (count($conditions['classroomIds']) == 0) {
                return $this->render('classroom-review/index.html.twig', array(
                    'reviews' => array(),
                    'users' => array(),
                    'classrooms' => array(),
                    'paginator' => new Paginator($request, 0, 20),
                ));
            }
        }

        $conditions['parentId'] = 0;
        $paginator = new Paginator(
            $request,
            $this->getClassroomReviewService()->searchReviewCount($conditions),
            20
        );

        $reviews = $this->getClassroomReviewService()->searchReviews(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($reviews, 'classroomId'));

        return $this->render('classroom-review/index.html.twig', array(
            'reviews' => $reviews,
            'users' => $users,
            'classrooms' => $classrooms,
            'paginator' => $paginator,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getClassroomReviewService()->deleteReview($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids as $id) {
            $this->getClassroomReviewService()->deleteReview($id);
        }

        return $this->createJsonResponse(true);
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getClassroomReviewService()
    {
        return $this->createService('Classroom:ClassroomReviewService');
    }
}
