<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;

class BaseActivityController extends BaseController
{
    public function learnDataDetailAction(Request $request, $task)
    {
        $conditions = array(
            'courseTaskId' => $task['id'],
        );

        $paginator = new Paginator(
            $request,
            $this->getTaskResultService()->countTaskResults($conditions),
            20
        );

        $taskResults = $this->getTaskResultService()->searchTaskResults(
            $conditions,
            array('createdTime' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($taskResults, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('activity/other-learn-data-detail-modal.html.twig', array(
            'task' => $task,
            'taskResults' => $taskResults,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
