<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use Biz\Announcement\Processor\AnnouncementProcessor;
use Biz\Announcement\Service\AnnouncementService;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AnnouncementController extends BaseController
{
    public function showAction(Request $request, $id, $targetId)
    {
        $announcement = $this->getAnnouncementService()->getAnnouncement($id);
        $processor = $this->getAnnouncementProcessor($announcement['targetType']);
        $targetObject = $processor->getTargetObject($targetId);

        return $this->render('announcement/announcement-show-modal.html.twig', [
            'announcement' => $announcement,
            'targetObject' => $targetObject,
        ]);
    }

    public function globalShowAction(Request $request, $id)
    {
        $announcement = $this->getAnnouncementService()->getAnnouncement($id);

        return $this->render('announcement/announcement-global-show-modal.html.twig', [
            'announcement' => $announcement,
        ]);
    }

    public function listAction(Request $request, $targetType, $targetId)
    {
        $processor = $this->getAnnouncementProcessor($targetType);
        $controller = $processor->getActions('list');

        return $this->forward($controller, [
            'request' => $request,
            'targetId' => $targetId,
        ]);
    }

    public function showAllAction(Request $request, $targetType, $targetId)
    {
        $conditions = [
            'targetType' => $targetType,
            'targetId' => $targetId,
        ];

        $announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, ['createdTime' => 'DESC'], 0, 10000);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($announcements, 'userId'));

        return $this->render('announcement/announcement-show-all-modal.html.twig', [
            'announcements' => $announcements,
            'users' => $users,
        ]);
    }

    public function manageAction(Request $request, $targetType, $targetId)
    {
        return $this->render('announcement/announcement-manage-modal.html.twig', [
            'targetId' => $targetId,
            'targetType' => $targetType,
        ]);
    }

    public function createAction(Request $request, $targetType, $targetId)
    {
        $processor = $this->getAnnouncementProcessor($targetType);
        $targetObject = $processor->tryManageObject($targetId);
        $controller = $processor->getActions('create');

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $data['targetType'] = $targetType;

            if ('course' == $targetType) {
                $data['targetId'] = empty($data['targetId']) ? $targetId : $data['targetId'];
            } else {
                $data['targetId'] = $targetId;
            }
            $data['url'] = isset($data['url']) ? $data['url'] : '';
            $data['startTime'] = isset($data['startTime']) ? strtotime($data['startTime']) : time();
            $data['endTime'] = isset($data['endTime']) ? strtotime($data['endTime']) : time();

            $announcement = $this->getAnnouncementService()->createAnnouncement($data);

            if ('notify' == $request->request->get('notify')) {
                $targetObjectShowRout = $processor->getTargetShowUrl();
                $targetObjectShowUrl = $this->generateUrl($targetObjectShowRout, ['id' => $targetId], UrlGeneratorInterface::ABSOLUTE_URL);
                if ($announcement['startTime'] <= time()) {
                    $processor->announcementNotification($targetId, $targetObject, $targetObjectShowUrl, $announcement);

                    return $this->createJsonResponse(true);
                }

                $this->getSchedulerService()->register([
                    'name' => 'announcement_notify_'.$announcement['id'],
                    'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                    'expression' => intval($announcement['startTime']),
                    'class' => 'Biz\Announcement\Job\AnnouncementNotifyJob',
                    'args' => ['targetId' => $targetId, 'targetType' => $targetType, 'params' => ['targetObject' => $targetObject, 'targetObjectShowUrl' => $targetObjectShowUrl, 'announcement' => $announcement]],
                    'misfire_threshold' => 60 * 60,
                ]);
            }

            return $this->createJsonResponse(true);
        }

        return $this->forward($controller, [
            'request' => $request,
            'targetId' => $targetId,
        ]);
    }

    public function updateAction(Request $request, $id, $targetType, $targetId)
    {
        $processor = $this->getAnnouncementProcessor($targetType);
        $targetObject = $processor->tryManageObject($targetId);

        $announcement = $this->getAnnouncementService()->getAnnouncement($id);
        if (!$announcement) {
            return $this->createMessageResponse('error', "Announcement(#{$id}) not found");
        }

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();

            $data['targetType'] = $targetType;
            if ('course' == $targetType) {
                $data['targetId'] = empty($data['targetId']) ? $targetId : $data['targetId'];
            } else {
                $data['targetId'] = $targetId;
            }
            $data['startTime'] = isset($data['startTime']) ? strtotime($data['startTime']) : time();
            $data['endTime'] = isset($data['endTime']) ? strtotime($data['endTime']) : time();

            $announcement = $this->getAnnouncementService()->updateAnnouncement($id, $data);

            $targetObjectShowRout = $processor->getTargetShowUrl();
            $targetObjectShowUrl = $this->generateUrl($targetObjectShowRout, ['id' => $targetId], UrlGeneratorInterface::ABSOLUTE_URL);

            $job = $this->getSchedulerService()->getJobByName('announcement_notify_'.$id);
            if (!empty($job)) {
                $this->getSchedulerService()->deleteJob($job['id']);
                $this->getSchedulerService()->register([
                    'name' => 'announcement_notify_'.$announcement['id'],
                    'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                    'expression' => intval($announcement['startTime']),
                    'class' => 'Biz\Announcement\Job\AnnouncementNotifyJob',
                    'args' => ['targetId' => $targetId, 'targetType' => $targetType, 'params' => ['targetObject' => $targetObject, 'targetObjectShowUrl' => $targetObjectShowUrl, 'announcement' => $announcement]],
                    'misfire_threshold' => 60 * 60,
                ]);
            }

            return $this->createJsonResponse(true);
        }

        $controller = $processor->getActions('edit');

        return $this->forward($controller, [
            'announcementId' => $announcement['id'],
            'targetId' => $targetId,
        ]);
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

    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }
}
