<?php

namespace OpenLivePlugin\Controller\MyTeaching;

use AppBundle\Common\Exception\AccessDeniedException;
use AppBundle\Common\Paginator;
use Biz\CloudPlatform\Service\AppService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use OpenLivePlugin\Biz\OpenLiveManage\Service\OpenLiveManageService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AdminV2\BaseController;

class OpenLiveController extends BaseController
{
    public function listAction(Request $request)
    {
        $conditions = array_merge($request->query->all(), [
            'speaker' => $this->getCurrentUser()->getId(),
            'publishStatus' => 'published',
            'sorts' => ['start_time' => 'desc']
        ]);
        if (!empty($conditions['page'])) {
            $offsetPage = (int) $conditions['page'] -1;
            $conditions['offset'] = ($offsetPage <= 0 ? 0 : $offsetPage)  * 20;
            $conditions['limit'] = 20;
        }
        $liveRoomData = $this->getOpenLiveManageService()->searchLives($conditions);
        $liveRooms = empty($liveRoomData['data']) ? [] : $liveRoomData['data'];
        $liveRoomCount = empty($liveRoomData['data']) ? 0 : $liveRoomData['paging']['total'];

        $paginator = new Paginator(
            $this->get('request'),
            $liveRoomCount,
            20
        );

        return $this->render('OpenLivePlugin::my-teaching/open-live-list.html.twig', [
            'liveRooms' => $this->transLiveRoomForList($liveRooms),
            'paginator' => $paginator,
            'serviceStatus' => 'on',
            'filter' => 'openLive'
        ]);
    }

    public function teachingAction(Request $request, $id)
    {
        $liveRoom = $this->getOpenLiveManageService()->getLive($id);
        if (!empty($liveRoom['errorMsg'])) {
            return $this->render('OpenLivePlugin::my-teaching/open-live-start-teaching.html.twig', [
                'clientEntryUrl' => '',
                'webEntryUrl' => '',
            ]);
        }
        if (empty($liveRoom['id'])) {
            throw new NotFoundException('公开课不存在');
        }
        if ($this->getCurrentUser()->getId() != $liveRoom['speaker']) {
            throw new AccessDeniedException('您无权限操作');
        }
        $entryUrl = $this->getOpenLiveManageService()->getLiveTeacherEntryUrl($id, $this->generateTeacherEntryFields());

        return $this->render('OpenLivePlugin::my-teaching/open-live-start-teaching.html.twig', [
            'clientEntryUrl' => $entryUrl['clientUrl'],
            'webEntryUrl' => $entryUrl['webUrl'],
        ]);
    }

    private function generateTeacherEntryFields()
    {
        $user = $this->getCurrentUser();

        return [
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'role' => 'teacher'
        ];
    }

    private function transLiveRoomForList($liveRooms)
    {
        $roomDataList = [];
        foreach ($liveRooms as $liveRoom) {
            $tmpData = [];
            $tmpData['id'] = $liveRoom['id'];
            $tmpData['title'] = $liveRoom['title'];
            $tmpData['room_cover'] = $liveRoom['room_cover'];
            $tmpData['summary'] = $liveRoom['summary'];
            $tmpData['live_status'] = $liveRoom['status'];
            $tmpData['plan_live_time'] = $this->dealLiveDateForListShow($liveRoom['start_time'], $liveRoom['end_time']);
            $tmpData['actual_live_time'] = $this->dealLiveDateForListShow($liveRoom['actual_start_time'], $liveRoom['actual_end_time']);
            $tmpData['enrolled_num'] = $liveRoom['enrolled_num'];
            $tmpData['visitor_total_num'] = $liveRoom['visitor_total_num'];

            $roomDataList[] = $tmpData;
        }

        return $roomDataList;
    }

    private function dealLiveDateForListShow($startTime, $endTime)
    {
        if (empty($startTime) || empty($endTime)) {
            return '';
        }
        $startDay = date('Ymd', $startTime);
        $endDay = date('Ymd', $endTime);
        if ($startDay == $endDay) {
            return date('Y-m-d ', $startTime).date('H:i~', $startTime).date('H:i', $endTime);
        }

        return date('Y-m-d H:i~', $startTime).date('Y-m-d H:i', $endTime);
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return OpenLiveManageService
     */
    protected function getOpenLiveManageService()
    {
        return $this->createService('OpenLivePlugin:OpenLiveManage:OpenLiveManageService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
