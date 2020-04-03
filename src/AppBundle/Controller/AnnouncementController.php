<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Announcement\Service\AnnouncementService;
use Biz\Announcement\Processor\AnnouncementProcessor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AnnouncementController extends BaseController
{
    public function showAction(Request $request, $id, $targetId)
    {
        $announcement = $this->getAnnouncementService()->getAnnouncement($id);
        $processor = $this->getAnnouncementProcessor($announcement['targetType']);
        $targetObject = $processor->getTargetObject($targetId);

        return $this->render('announcement/announcement-show-modal.html.twig', array(
            'announcement' => $announcement,
            'targetObject' => $targetObject,
        ));
    }

    public function globalShowAction(Request $request, $id)
    {
        $announcement = $this->getAnnouncementService()->getAnnouncement($id);

        return $this->render('announcement/announcement-global-show-modal.html.twig', array(
            'announcement' => $announcement,
        ));
    }

    public function listAction(Request $request, $targetType, $targetId)
    {
        $processor = $this->getAnnouncementProcessor($targetType);
        $controller = $processor->getActions('list');

        return $this->forward($controller, array(
            'request' => $request,
            'targetId' => $targetId,
        ));
    }

    public function showAllAction(Request $request, $targetType, $targetId)
    {
        $conditions = array(
            'targetType' => $targetType,
            'targetId' => $targetId,
        );

        $announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, array('createdTime' => 'DESC'), 0, 10000);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($announcements, 'userId'));

        return $this->render('announcement/announcement-show-all-modal.html.twig', array(
            'announcements' => $announcements,
            'users' => $users,
        ));
    }

    public function manageAction(Request $request, $targetType, $targetId)
    {
        return $this->render('announcement/announcement-manage-modal.html.twig', array(
            'targetId' => $targetId,
            'targetType' => $targetType,
        ));
    }

    public function createAction(Request $request, $targetType, $targetId)
    {
        $processor = $this->getAnnouncementProcessor($targetType);
        $targetObject = $processor->tryManageObject($targetId);
        $controller = $processor->getActions('create');

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data['targetType'] = $targetType;

            if ($targetType == 'course') {
                $data['targetId'] = empty($data['targetId']) ? $targetId : $data['targetId'];
            } else {
                $data['targetId'] = $targetId;
            }
            $data['url'] = isset($data['url']) ? $data['url'] : '';
            $data['startTime'] = isset($data['startTime']) ? strtotime($data['startTime']) : time();
            $data['endTime'] = isset($data['endTime']) ? strtotime($data['endTime']) : time();

            $announcement = $this->getAnnouncementService()->createAnnouncement($data);

            if ($request->request->get('notify') == 'notify') {
                $targetObjectShowRout = $processor->getTargetShowUrl();
                $targetObjectShowUrl = $this->generateUrl($targetObjectShowRout, array('id' => $targetId), UrlGeneratorInterface::ABSOLUTE_URL);

                $result = $processor->announcementNotification($targetId, $targetObject, $targetObjectShowUrl);
            }

            return $this->createJsonResponse(true);
        }

        return $this->forward($controller, array(
            'request' => $request,
            'targetId' => $targetId,
        ));
    }

    public function updateAction(Request $request, $id, $targetType, $targetId)
    {
        $processor = $this->getAnnouncementProcessor($targetType);
        $targetObject = $processor->tryManageObject($targetId);

        $announcement = $this->getAnnouncementService()->getAnnouncement($id);
        if (!$announcement) {
            return $this->createMessageResponse('error', "Announcement(#{$id}) not found");
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $data['targetType'] = $targetType;
            if ($targetType == 'course') {
                $data['targetId'] = empty($data['targetId']) ? $targetId : $data['targetId'];
            } else {
                $data['targetId'] = $targetId;
            }
            $data['startTime'] = isset($data['startTime']) ? strtotime($data['startTime']) : time();
            $data['endTime'] = isset($data['endTime']) ? strtotime($data['endTime']) : time();

            $this->getAnnouncementService()->updateAnnouncement($id, $data);

            return $this->createJsonResponse(true);
        }

        $controller = $processor->getActions('edit');

        return $this->forward($controller, array(
            'announcementId' => $announcement['id'],
            'targetId' => $targetId,
        ));
    }

    public function deleteAction(Request $request, $id, $targetType, $targetId)
    {
        $processor = $this->getAnnouncementProcessor($targetType);
        $targetObject = $processor->tryManageObject($targetId);

        $this->getAnnouncementService()->deleteAnnouncement($id);

        return $this->createJsonResponse(true);
    }

    /**
     * @param  $targetType
     *
     * @return AnnouncementProcessor
     */
    protected function getAnnouncementProcessor($targetType)
    {
        $biz = $this->get('biz');
        $processor = $biz['announcement_processor']->create($targetType);

        return $processor;
    }

    /**
     * @return AnnouncementService
     */
    protected function getAnnouncementService()
    {
        return $this->get('biz')->service('Announcement:AnnouncementService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->get('biz')->service('User:UserService');
    }
}
