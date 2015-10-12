<?php
namespace Classroom\ClassroomBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ClassroomThreadAdminController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        // if (isset($conditions['keywordType']) && $conditions['keywordType'] == 'courseTitle') {
        // }

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );
        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($threads, 'targetId'));

        return $this->render('ClassroomBundle:ClassroomAdmin:thread-list.html.twig', array(
            'paginator' => $paginator,
            'threads' => $threads,
            'users' => $users,
            'classrooms' => $classrooms,
        ));
    }

    public function deleteAction(Request $request, $threadId)
    {
        $this->getThreadService()->deleteThread($threadId);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids ?: array() as $id) {
            $this->getThreadService()->deleteThread($id);
        }

        return $this->createJsonResponse(true);
    }
    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }
}
