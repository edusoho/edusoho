<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class NotificationController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $paginator = new Paginator(
            $request,
            $this->getNotificationService()->getUserNotificationCount($user->id),
            20
        );

        $notifications = $this->getNotificationService()->findUserNotifications(
            $user->id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $this->getNotificationService()->clearUserNewNotificationCounter($user->id);
        $user->clearNotifacationNum();

        return $this->render('TopxiaWebBundle:Notification:index.html.twig', array(
            'notifications' => $notifications,
            'paginator'     => $paginator
        ));
    }

    public function showAction(Request $request, $id)
    {
        $batchnotification = $this->getBatchNotificationService()->getBatchNotification($id);
        return $this->render('TopxiaWebBundle:Notification:batch-notification-show.html.twig', array(
            'batchnotification' => $batchnotification
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User:NotificationService');
    }

    protected function getBatchNotificationService()
    {
        return $this->getServiceKernel()->createService('User:BatchNotificationService');
    }
}
