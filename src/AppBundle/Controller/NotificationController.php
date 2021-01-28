<?php

namespace AppBundle\Controller;

use Biz\Course\Service\CourseService;
use Biz\User\Service\BatchNotificationService;
use Biz\User\Service\NotificationService;
use AppBundle\Common\Paginator;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

class NotificationController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $paginator = new Paginator(
            $request,
            $this->getNotificationService()->countNotificationsByUserId($user->id),
            20
        );

        $notifications = $this->getNotificationService()->searchNotificationsByUserId(
            $user->id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $id = $request->query->get('id');
        if (!empty($id)) {
            $notifications = $this->getNotificationService()->isSelectNotification($notifications, $id);
        }
        $this->getNotificationService()->clearUserNewNotificationCounter($user->id);
        $user->clearNotifacationNum();

        return $this->render('notification/index.html.twig', array(
            'notifications' => $notifications,
            'paginator' => $paginator,
        ));
    }

    public function showAction(Request $request, $id)
    {
        $batchNotification = $this->getBatchNotificationService()->getBatchNotification($id);

        return $this->render('notification/batch-notification-show.html.twig', array(
            'batchnotification' => $batchNotification,
        ));
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return BatchNotificationService
     */
    protected function getBatchNotificationService()
    {
        return  $this->getBiz()->service('User:BatchNotificationService');
    }
}
