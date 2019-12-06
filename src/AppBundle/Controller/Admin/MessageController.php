<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class MessageController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['isDelete'] = 0; // 默认显示未删除的数据

        $paginator = new Paginator(
            $request,
            $this->getMessageService()->countMessages($conditions),
            20
        );

        $messages = $this->getMessageService()->searchMessages(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array_merge(ArrayToolkit::column($messages, 'fromId'), ArrayToolkit::column($messages, 'toId'));

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin/message/index.html.twig', array(
            'users' => $users,
            'messages' => $messages,
            'paginator' => $paginator, ));
    }

    public function deleteChoosedMessagesAction(Request $request)
    {
        $ids = $request->request->get('ids');
        $result = $this->getMessageService()->deleteMessagesByIds($ids);

        if ($result) {
            return $this->createJsonResponse(array('status' => 'failed'));
        } else {
            return $this->createJsonResponse(array('status' => 'success'));
        }
    }

    protected function getMessageService()
    {
        return $this->createService('User:MessageService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
