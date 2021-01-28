<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Biz\User\Service\MessageService;
use Biz\User\Service\UserService;
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

        return $this->render('admin-v2/operating/message/index.html.twig', array(
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

    /**
     * @return MessageService
     */
    protected function getMessageService()
    {
        return $this->createService('User:MessageService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
