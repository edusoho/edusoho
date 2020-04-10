<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Notification\NotificationException;
use Symfony\Component\HttpFoundation\Request;

class BatchNotificationController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $conditions = array();
        $paginator = new Paginator(
            $request,
            $this->getBatchNotificationService()->countBatchNotifications($conditions),
            10
        );
        $batchnotifications = $this->getBatchNotificationService()->searchBatchNotifications(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $userIds = ArrayToolkit::column($batchnotifications, 'fromId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin/notification/index.html.twig', array(
            'paginator' => $paginator,
            'batchnotifications' => $batchnotifications,
            'users' => $users,
        ));
    }

    public function createAction(Request $request)
    {
        $user = $this->getUser();

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();

            if (empty($formData['title']) || empty($formData['content'])) {
                $this->createMessageResponse('error', 'message_response.mass_title_and_text_cannot_empty');
            }

            $this->getBatchNotificationService()->createBatchNotification($formData);

            return $this->redirect($this->generateUrl('admin_batch_notification'));
        }

        return $this->render('admin/notification/notification-modal.html.twig');
    }

    public function editAction(Request $request, $id)
    {
        $user = $this->getUser();
        $batchnotification = $this->getBatchNotificationService()->getBatchNotification($id);
        if (empty($batchnotification)) {
            $this->createNewException(NotificationException::BATCH_NOTIFICATION_NOT_FOUND());
        }
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $this->getBatchNotificationService()->updateBatchNotification($id, $formData);

            return $this->redirect($this->generateUrl('admin_batch_notification'));
        }

        return $this->render('admin/notification/notification-modal.html.twig', array(
            'batchnotification' => $batchnotification,
        ));
    }

    public function sendAction(Request $request, $id)
    {
        if ($request->getMethod() == 'POST') {
            $this->getBatchNotificationService()->publishBatchNotification($id);
        }

        return $this->createJsonResponse(array('status' => 'success'));
    }

    public function deleteAction(Request $request, $id)
    {
        if ($request->getMethod() == 'POST') {
            $result = $this->getBatchNotificationService()->deleteBatchNotification($id);
            if ($result) {
                return $this->createJsonResponse(array('status' => 'failed'));
            } else {
                return $this->createJsonResponse(array('status' => 'success'));
            }
        }
    }

    public function showAction(Request $request, $id)
    {
        $batchnotification = $this->getBatchNotificationService()->getBatchNotification($id);
        if (empty($batchnotification)) {
            $this->createNewException(NotificationException::BATCH_NOTIFICATION_NOT_FOUND());
        }

        return $this->render('admin/notification/notification-modal.html.twig', array(
            'batchnotification' => $batchnotification,
        ));
    }

    protected function getBatchNotificationService()
    {
        return $this->createService('User:BatchNotificationService');
    }
}
