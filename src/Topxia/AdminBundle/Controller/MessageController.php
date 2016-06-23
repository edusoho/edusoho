<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class MessageController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields     = $request->query->all();

        $conditions = array(
            'content'   => '',
            'nickname'  => '',
            'startDate' => 0,
            'endDate'   => 0,
        );

        $conditions = array_merge($conditions, $fields);

        $conditions = $this->convertConditions($fields);
        if(isset($conditions['fromIds']) && empty($conditions['fromIds'])){
            $paginator = new Paginator($request, 0, 20);
            $messages = array();
        }else{
            $paginator = new Paginator(
                $request,
                $this->getMessageService()->searchMessagesCount($conditions),
                20
            );

            $messages = $this->getMessageService()->searchMessages(
                $conditions,
                null,
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }


        $usersFromId = $this->getUserService()->findUsersByIds(ArrayToolkit::column($messages, 'fromId'));
        $usersToId   = $this->getUserService()->findUsersByIds(ArrayToolkit::column($messages, 'toId'));
        $users       = ArrayToolkit::index(array_merge($usersFromId, $usersToId), 'id');

        return $this->render('TopxiaAdminBundle:Message:index.html.twig', array(
            'users'     => $users,
            'messages'  => $messages,
            'paginator' => $paginator));
    }

    public function deleteChoosedMessagesAction(Request $request)
    {
        $ids    = $request->request->get('ids');
        $result = $this->getMessageService()->deleteMessagesByIds($ids);

        if ($result) {
            return $this->createJsonResponse(array('status' => 'failed'));
        } else {
            return $this->createJsonResponse(array('status' => 'success'));
        }
    }

    protected function convertConditions($conditions)
    {
        if (!empty($conditions['nickname'])) {
            $conditions['fromIds']= "";
            
            $userConditions = array('nickname' => trim($conditions['nickname']));
            $userCount = $this->getUserService()->searchUserCount($userConditions);
            if ($userCount) {
                $users                 = $this->getUserService()->searchUsers($userConditions, array('createdTime', 'DESC'), 0, $userCount);
                $conditions['fromIds'] = ArrayToolkit::column($users, 'id');
            }
            
        }

        unset($conditions['nickname']);
      
        if (!empty($conditions['startDate'])  ) {
            $conditions['startDate'] = strtotime($conditions['startDate']);
        }

        if (!empty($conditions['endDate'])) {
            $conditions['endDate'] = strtotime($conditions['endDate']);
        }

        return $conditions;
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getFileService()->deleteFile($id);

        return $this->createNewJsonResponse(true);
    }

    public function uploadAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:File:upload-modal.html.twig');
    }

    protected function getMessageService()
    {
        return $this->getServiceKernel()->createService('User.MessageService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
