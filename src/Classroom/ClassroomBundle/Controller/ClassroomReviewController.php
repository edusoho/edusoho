<?php
namespace Classroom\ClassroomBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\AdminBundle\Controller\BaseController;

class ClassroomReviewController extends BaseController
{

    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        if (!empty($conditions['classroomTitle'])) {
            $classrooms = $this->getClassroomService()->findClassroomsByLikeTitle(trim($conditions['classroomTitle']));
            $conditions['classroomIds'] = ArrayToolkit::column($classrooms, 'id');
            if (count($conditions['classroomIds']) == 0) {
                return $this->render('ClassroomBundle:ClassroomReview:index.html.twig', array(
                'reviews' => array(),
                'users' => array(),
                'classrooms' => array(),
                'paginator' => new Paginator($request, 0, 20),
                ));
            }
        }

        $paginator = new Paginator(
            $request,
            $this->getClassroomReviewService()->searchReviewCount($conditions),
            20
        );
        $reviews = $this->getClassroomReviewService()->searchReviews(
            $conditions,
            array('createdTime', 'DESC' ),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($reviews, 'classroomId'));

        return $this->render('ClassroomBundle:ClassroomReview:index.html.twig', array(
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
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getClassroomReviewService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomReviewService');
    }
}
