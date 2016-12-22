<?php
namespace AppBundle\Controller;

use Biz\Course\Service\CourseService;
use Biz\User\Service\BatchNotificationService;
use Biz\User\Service\NotificationService;
use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;


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
            $this->getNotificationService()->countNotificationsByUserId($user->id),
            20
        );

        $notifications = $this->getNotificationService()->searchNotificationsByUserId(
            $user->id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $this->getNotificationService()->clearUserNewNotificationCounter($user->id);
        $user->clearNotifacationNum();

        return $this->render('notification/index.html.twig', array(
            'notifications' => $notifications,
            'paginator'     => $paginator
        ));
    }

    public function showAction(Request $request, $id)
    {
        $batchnotification = $this->getBatchNotificationService()->getBatchNotification($id);
        return $this->render('notification/batch-notification-show.html.twig', array(
            'batchnotification' => $batchnotification
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
