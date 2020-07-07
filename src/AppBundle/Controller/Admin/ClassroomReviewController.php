<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Review\Service\ReviewService;
use Symfony\Component\HttpFoundation\Request;

class ClassroomReviewController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = array_merge(['targetType' => 'classroom'], $request->query->all());

        if (empty($conditions['rating'])) {
            unset($conditions['rating']);
        }

        if (!empty($conditions['classroomTitle'])) {
            $classrooms = $this->getClassroomService()->findClassroomsByLikeTitle(trim($conditions['classroomTitle']));
            $conditions['targetIds'] = ArrayToolkit::column($classrooms, 'id');
            if (0 == count($conditions['targetIds'])) {
                return $this->render('classroom-review/index.html.twig', [
                    'reviews' => [],
                    'users' => [],
                    'classrooms' => [],
                    'paginator' => new Paginator($request, 0, 20),
                ]);
            }
        }

        $conditions['parentId'] = 0;
        $paginator = new Paginator(
            $request,
            $this->getReviewService()->countReviews($conditions),
            20
        );

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($reviews, 'targetId'));

        return $this->render('classroom-review/index.html.twig', [
            'reviews' => $reviews,
            'users' => $users,
            'classrooms' => $classrooms,
            'paginator' => $paginator,
        ]);
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

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Review:ReviewService');
    }
}
