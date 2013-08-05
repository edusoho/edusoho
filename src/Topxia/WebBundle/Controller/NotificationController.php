<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class NotificationController extends BaseController
{

    public function indexAction (Request $request)
    {
        $user = $this->getCurrentUser();
        
        $paginator = new Paginator(
            $request,
            $this->getNotificationService()->getUserNotificationCount($user->id),
            10
        );

        $notifications = $this->getNotificationService()->findUserNotifications(
            $user->id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        return $this->render('TopxiaWebBundle:Notification:index.html.twig', array(
            'notifications' => $notifications,
            'paginator' => $paginator
        ));
    }


    protected function getUserService(){
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getNotificationService(){
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}