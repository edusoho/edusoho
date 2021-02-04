<?php

namespace OpenLivePlugin\Controller\Admin;

use AppBundle\Common\Exception\AccessDeniedException;
use AppBundle\Common\Paginator;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\AppService;
use Biz\Content\Service\FileService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Endroid\QrCode\QrCode;
use OpenLivePlugin\Biz\Cash\Service\CashService;
use OpenLivePlugin\Biz\OpenLiveManage\Service\OpenLiveManageService;
use OpenLivePlugin\Biz\User\Service\PluginUserService;
use OpenLivePlugin\Common\ArrayToolkit;
use OpenLivePlugin\Common\FileToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Response;

class OpenLiveController extends BaseController
{
    public function listAction(Request $request)
    {
        $apps = $this->getAppService()->getCenterApps();

        if (isset($apps['error']) || empty($apps)) {
            return $this->render('OpenLivePlugin::admin/open-live-unlink-cloud.html.twig');
        }

        $conditions = $request->query->all();
        $conditions['offset'] = 0;
        $conditions['limit'] = 20;
        if (!empty($conditions['page'])) {
            $offsetPage = (int) $conditions['page'] -1;
            $conditions['offset'] = ($offsetPage <= 0 ? 0 : $offsetPage)  * 20;
        }
        $liveRoomData = $this->getOpenLiveManageService()->searchLives($conditions);
        $liveRooms = empty($liveRoomData['data']) ? [] : $liveRoomData['data'];
        $liveRoomCount = empty($liveRoomData['data']) ? 0 : $liveRoomData['paging']['total'];

        $paginator = new Paginator(
            $this->get('request'),
            $liveRoomCount,
            20
        );
        return $this->render('OpenLivePlugin::admin/open-live-index.html.twig', [
            'liveRooms' => $this->transLiveRoomForList($liveRooms),
            'paginator' => $paginator,
        ]);
    }

    public function createLiveAction(Request $request)
    {
        $apps = $this->getAppService()->getCenterApps();

        if (isset($apps['error']) || empty($apps)) {
            return $this->render('OpenLivePlugin::admin/open-live-unlink-cloud.html.twig');
        }
        if ($this->isArrearage()) {
            return $this->render('OpenLivePlugin::admin/open-live-create-denied.html.twig', [
                'rechargeUrl' => $this->getAppService()->getTokenLoginUrl('order_recharge', [])
            ]);
        }

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            $result = $this->getOpenLiveManageService()->createLive($data);

            return $this->createJsonResponse($result);
        }
        $smsSettingWarningMsg = $this->tryGetSmsSettingWarningMsg();

        return $this->render('OpenLivePlugin::admin/open-live-create.html.twig', [
            'liveRoom' => [],
            'smsSettingWarningMsg' => $smsSettingWarningMsg
        ]);
    }

    public function coverCropAction(Request $request)
    {
        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            $allTypeImages = ArrayToolkit::index($data['images'], 'type');
            $file = $this->getFileService()->getFile($allTypeImages['large']['id']);
            $fileOssUrl = $this->getOpenLiveManageService()->generateOssFile($file['uri'], 'open-live/room-cover/', $file['id']);

            return $this->createJsonResponse(['image' => $fileOssUrl]);
        }

        return $this->render('OpenLivePlugin::admin/open-live-cover-crop-modal.html.twig');
    }

    public function editAction(Request $request, $id)
    {
        $apps = $this->getAppService()->getCenterApps();

        if (isset($apps['error']) || empty($apps)) {
            return $this->render('OpenLivePlugin::admin/open-live-unlink-cloud.html.twig');
        }
        if ($this->isArrearage()) {
            throw new AccessDeniedException('账户欠费无法使用');
        }
        $liveRoom = $this->getOpenLiveManageService()->getLive($id);
        if ('finished' === $liveRoom['status'] || $liveRoom['end_time'] < time()) {
            throw new AccessDeniedException('已到结束时间的公开课不允许编辑');
        }

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            if (time() > $liveRoom['start_time']) {
                unset($data['startDate']);
            }
            $result = $this->getOpenLiveManageService()->editLive($data);

            return $this->createJsonResponse($result);
        }
        $smsSettingWarningMsg = $this->tryGetSmsSettingWarningMsg();

        return $this->render('OpenLivePlugin::admin/open-live-create.html.twig', [
            'liveRoom' => $this->transLiveRoomForEdit($liveRoom),
            'isArrearage' => $this->isArrearage(),
            'smsSettingWarningMsg' => $smsSettingWarningMsg
        ]);
    }

    public function qrcodeAction(Request $request, $id)
    {
        $text = $request->get('text');
        $qrCode = new QrCode();
        $qrCode->setText($text);
        $qrCode->setSize(250);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');

        $headers = array(
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="qrcode.png"',
        );

        return new Response($img, 200, $headers);
    }

    public function detailAction(Request $request, $id)
    {
        $liveRoom = $this->getOpenLiveManageService()->getLive($id);
        $isArrearage = $this->isArrearage();
        if ($isArrearage || $liveRoom['status'] !== 'finished' || $liveRoom['charge_status'] !== 'paid') {
            return $this->render('OpenLivePlugin::admin/open-live-statistic-detail-denied.html.twig', [
                'liveRoom' => $this->transLiveRoomForDetail($liveRoom),
                'rechargeUrl' => $this->getAppService()->getTokenLoginUrl('order_recharge', []),
                'isArrearage' => $isArrearage
            ]);
        }
        $liveOnlineNumRecords = $this->getOpenLiveManageService()->searchLiveOnlineNumRecords($id);
        $liveMemberAnalysisData = $this->getOpenLiveManageService()->getMemberAnalysisData($id);

        $liveVisitorReports = $this->getOpenLiveManageService()->getLiveVisitorReport($id, ['offset' => 0, 'limit' => 10]);
        $liveVisitors = empty($liveVisitorReports['data']) ? [] : $liveVisitorReports['data'];
        $liveVisitorCount = empty($liveVisitorReports['data']) ? 0 : $liveVisitorReports['paging']['total'];
        $paginator = new Paginator(
            $this->get('request'),
            $liveVisitorCount,
            10
        );
        $paginator->setBaseUrl($this->generateUrl('admin_v2_open_live_visitor_reports_search', ['id' => $id]));

        return $this->render('OpenLivePlugin::admin/open-live-statistic-detail.html.twig', [
            'liveRoom' => $this->transLiveRoomForDetail($liveRoom),
            'liveMemberAnalysisData' => $this->transAnalysisDataToFunnelShow($liveMemberAnalysisData),
            'liveOnlineNumRecords' => json_encode($this->transLiveOnlineNumRecordsForDetail($liveOnlineNumRecords)),
            'liveVisitorReports' => $this->transLiveVisitorsForDetail($liveVisitors),
            'paginator' => $paginator
        ]);
    }

    public function searchLiveOnlineNumRecordsAction(Request $request, $id)
    {
        $this->getOpenLiveManageService()->checkLiveRoomDetailAccess($id);
        $conditions['startData'] = $request->query->get('startData', '');
        $conditions['endData'] = $request->query->get('endData', '');
        $liveOnlineNumRecords = $this->getOpenLiveManageService()->searchLiveOnlineNumRecords($id, $conditions);

        return $this->createJsonResponse($this->transLiveOnlineNumRecordsForDetail($liveOnlineNumRecords));
    }

    public function searchLiveVisitorReportsAction(Request $request, $id)
    {
        $this->getOpenLiveManageService()->checkLiveRoomDetailAccess($id);
        $conditions = $request->query->all();
        $offsetPage = (int) (empty($conditions['page']) ? 0 : $conditions['page']);
        $conditions['offset'] = (($offsetPage <= 1 ? 1 : $offsetPage) - 1) * 10;
        $conditions['limit'] = 10;

        $liveVisitorReports = $this->getOpenLiveManageService()->getLiveVisitorReport($id, $conditions);
        $liveVisitors = empty($liveVisitorReports['data']) ? [] : $liveVisitorReports['data'];
        $liveVisitorCount = empty($liveVisitorReports['data']) ? 0 : $liveVisitorReports['paging']['total'];
        $paginator = new Paginator(
            $request,
            $liveVisitorCount,
            10
        );

        return $this->render('OpenLivePlugin::admin/open-live-statistic-visitor-reports.html.twig', [
            'liveVisitorReports' => $this->transLiveVisitorsForDetail($liveVisitors),
            'paginator' => $paginator
        ]);
    }

    public function shareAction(Request $request, $id)
    {
        $shareUrl = $this->getOpenLiveManageService()->getLiveShareUrl($id);
        $shareSetting = $this->getOpenLiveManageService()->getWeChatShareSettingByLiveId($id);

        return $this->render('OpenLivePlugin::admin/open-live-share-modal.html.twig', [
            'shareUrl' => $shareUrl['share_url'],
            'shareSetting' => $shareSetting,
            'liveRoomId' => $id
        ]);
    }

    public function weChatShareImageUploadAction(Request $request, $id)
    {
        $file = $request->files->get('wechatShare');

        if (!FileToolkit::isImageFile($file)) {
            throw new AccessDeniedException('image invalid');
        }
        if ($file->getSize() > 1048576) {
            return $this->createJsonResponse(['url' => '', 'errorMsg' => '上传失败，图片最大允许1M！']);
        }
        $generatedFileName = md5('weChatShare'.$id.time());
        $params = [
            'name' => $generatedFileName,
            'reskey' => 'open-live/wechat-share/'.$generatedFileName.'.'.$file->getClientOriginalExtension(),
            'extno' => $id
        ];
        $initFileInfo = $this->getOpenLiveManageService()->initUploadFile($params);
        $upload = $this->getOpenLiveManageService()->uploadFile($initFileInfo, $file);

        return $this->createJsonResponse([
            'url' => $initFileInfo['domain'].'/'.$upload['key']
        ]);
    }

    public function roomWeChatShareSaveAction(Request $request, $id)
    {
        $result = $this->getOpenLiveManageService()->saveLiveWeChatShareSetting($id, $request->request->all());

        return $this->createJsonResponse($result);
    }

    public function alertLiveDetailMessageAction(Request $request, $id)
    {
        $liveRoom = $this->getOpenLiveManageService()->getLive($id);

        return $this->render('OpenLivePlugin::admin/open-live-operation-notice-modal.html.twig', [
            'liveStatus' => $liveRoom['status'],
            'liveChargeStatus' => $liveRoom['charge_status']
        ]);
    }

    public function teachingAction(Request $request, $id)
    {
        $entryUrl = $this->getOpenLiveManageService()->getLiveTeacherEntryUrl($id, $this->generateTeacherEntryFields());

        return $this->render('OpenLivePlugin::admin/open-live-start-teaching.html.twig', [
            'clientEntryUrl' => $entryUrl['clientUrl'],
            'webEntryUrl' => $entryUrl['webUrl'],
        ]);
    }

    public function publishAction(Request $request, $id)
    {
        return $this->createJsonResponse([
            'liveRoomId' => $id,
            'data' => $this->getOpenLiveManageService()->publishLive($id),
        ]);
    }

    public function unpublishAction(Request $request, $id)
    {
        $liveRoom = $this->getOpenLiveManageService()->getLive($id);
        if (empty($liveRoom)) {
            throw new NotFoundException();
        }

        return $this->createJsonResponse([
            'liveRoomId' => $id,
            'data' => $this->getOpenLiveManageService()->unpublishLive($id),
            'canDelete' => 'unstart' === $liveRoom['status']
        ]);
    }

    public function closeAction(Request $request, $id)
    {
        return $this->createJsonResponse([
            'liveRoomId' => $id,
            'data' => $this->getOpenLiveManageService()->closeLive($id)
        ]);
    }

    public function deleteAction(Request $request, $id)
    {
        $liveRoom = $this->getOpenLiveManageService()->getLive($id);
        if (empty($liveRoom)) {
            throw new NotFoundException();
        }
        if (1 === $liveRoom['is_published'] || 'unstart' !== $liveRoom['status']) {
            return $this->createJsonResponse([
                'liveRoomId' => $id,
                'data' => ['success' => false, 'errorMsg' => '无法删除发布或正在直播的公开课！'],
            ]);
        }

        return $this->createJsonResponse([
            'liveRoomId' => $id,
            'data' => $this->getOpenLiveManageService()->deleteLive($id),
        ]);
    }

    public function speakerMatchAction(Request $request)
    {
        $searchStr = $request->query->get('q', '');
        $speakers = $this->getPluginUserService()->searchSpeakers($searchStr);
        $data = [];
        foreach ($speakers as $speaker) {
            $data[] = [
                'id' => $speaker['id'],
                'name' => $speaker['nickname'],
                'avatar' => $this->getWebExtension()->getFilePath($speaker['smallAvatar'], 'avatar.png'),
                'mobile' => $speaker['verifiedMobile'],
                'email' => $speaker['email']
            ];
        }

        return $this->createJsonResponse($data);
    }

    public function smsReachedStudentListAction(Request $request, $id)
    {
        $smsReachedStudentData = $this->getOpenLiveManageService()->searchSmsReachedRoomStudent($id, ['offset' => 0, 'limit' => 20]);
        $smsReachedStudents = empty($smsReachedStudentData['data']) ? [] : $smsReachedStudentData['data'];
        $smsReachedStudentsCount = empty($smsReachedStudentData['data']) ? 0 : $smsReachedStudentData['paging']['total'];

        $paginator = new Paginator(
            $this->get('request'),
            $smsReachedStudentsCount,
            20
        );
        $paginator->setBaseUrl($this->generateUrl('admin_v2_open_live_sms_reached_student_ajax_search', ['id' => $id]));

        return $this->render('OpenLivePlugin::admin/sms-reached-student/open-live-sms-reached-student-list.html.twig', array(
            'smsReachedStudents' => $smsReachedStudents,
            'paginator' => $paginator,
            'success' => $smsReachedStudentData['success'],
            'roomId' => $id
        ));
    }

    public function smsReachedStudentAjaxSearchAction(Request $request, $id)
    {
        $keyword = $request->get('keyword', '');
        $page = $request->get('page', '') <= 1 ? 1 : (int) $request->get('page', '');
        $conditions = [];
        if (!empty($keyword)) {
            $conditions['mobile'] = $keyword;
        }
        if (!empty($page)) {
            $conditions['offset'] = ($page - 1) * 20;
            $conditions['limit'] = 20;
        }

        $smsReachedStudentData = $this->getOpenLiveManageService()->searchSmsReachedRoomStudent($id, $conditions);
        $smsReachedStudents = empty($smsReachedStudentData['data']) ? [] : $smsReachedStudentData['data'];
        $smsReachedStudentsCount = empty($smsReachedStudentData['data']) ? 0 : $smsReachedStudentData['paging']['total'];

        $paginator = new Paginator(
            $this->get('request'),
            $smsReachedStudentsCount,
            20
        );

        return $this->render('OpenLivePlugin::admin/sms-reached-student/open-live-sms-reached-student-tr.html.twig', array(
            'smsReachedStudents' => $smsReachedStudents,
            'paginator' => $paginator,
            'success' => $smsReachedStudentData['success'],
            'roomId' => $id
        ));
    }

    public function enrolledStudentListAction(Request $request, $id)
    {
        $enrolledStudentData = $this->getOpenLiveManageService()->searchEnrolledRoomStudent($id, ['offset' => 0, 'limit' => 20]);
        $enrolledStudents = empty($enrolledStudentData['data']) ? [] : $enrolledStudentData['data'];
        $enrolledStudentsCount = empty($enrolledStudentData['data']) ? 0 : $enrolledStudentData['paging']['total'];

        $paginator = new Paginator(
            $this->get('request'),
            $enrolledStudentsCount,
            20
        );
        $paginator->setBaseUrl($this->generateUrl('admin_v2_open_live_enrolled_student_ajax_search', ['id' => $id]));

        return $this->render('OpenLivePlugin::admin/enrolled-student/open-live-enrolled-student-list.html.twig', array(
            'enrolledStudents' => $enrolledStudents,
            'paginator' => $paginator,
            'success' => $enrolledStudentData['success'],
            'roomId' => $id
        ));
    }

    public function enrolledStudentAjaxSearchAction(Request $request, $id)
    {
        $keywordType = $request->get('keywordType', '');
        $keyword = $request->get('keyword', '');
        $page = $request->get('page', '') <= 1 ? 1 : (int) $request->get('page', '');
        $conditions = [];
        if (!empty($keyword)) {
            $conditions[$keywordType] = $keyword;
        }
        if (!empty($page)) {
            $conditions['offset'] = ($page - 1) * 20;
            $conditions['limit'] = 20;
        }
        $enrolledStudentData = $this->getOpenLiveManageService()->searchEnrolledRoomStudent($id, $conditions);
        $enrolledStudents = empty($enrolledStudentData['data']) ? [] : $enrolledStudentData['data'];
        $enrolledStudentsCount = empty($enrolledStudentData['data']) ? 0 : $enrolledStudentData['paging']['total'];

        $paginator = new Paginator(
            $this->get('request'),
            $enrolledStudentsCount,
            20
        );

        return $this->render('OpenLivePlugin::admin/enrolled-student/open-live-enrolled-student-tr.html.twig', array(
            'enrolledStudents' => $enrolledStudents,
            'paginator' => $paginator,
            'success' => $enrolledStudentData['success'],
            'roomId' => $id
        ));
    }

    private function transLiveRoomForList($liveRooms)
    {
        if (empty($liveRooms)) {
            return $liveRooms;
        }

        $speakerIds = ArrayToolkit::column($liveRooms, 'speaker');
        $speakersIndex = ArrayToolkit::index($this->getUserService()->findUsersByIds($speakerIds), 'id');

        foreach ($liveRooms as &$liveRoom) {
            $liveRoom['live_status'] = $liveRoom['status'];
            $liveRoom['is_live_finished'] = (('finished' === $liveRoom['status']) || ($liveRoom['end_time'] < time()));
            $liveRoom['settle_status'] = $liveRoom['charge_status'];
            $liveRoom['publish_status'] = empty($liveRoom['is_published']) ? 'unpublish' : 'published';
            $liveRoom['plan_live_time'] = $this->dealLiveDateForListShow($liveRoom['start_time'], $liveRoom['end_time']);
            $liveRoom['actual_live_time'] = $this->dealLiveDateForListShow($liveRoom['actual_start_time'], $liveRoom['actual_end_time']);
            $liveRoom['speaker_name'] = empty($speakersIndex[$liveRoom['speaker']]) ? '' : $speakersIndex[$liveRoom['speaker']]['nickname'];
        }

        return $liveRooms;
    }

    private function isArrearage()
    {
        $account = $this->getCashService()->getCashAccount();
        if (!empty($account)) {
            return $account['arrearage']*100>0 ? 1 : 0;
        }

        return false;
    }
    private function transLiveRoomForEdit($liveRoom)
    {
        $liveRoom['startDate'] = date('Y-m-d H:i', $liveRoom['start_time']);
        $liveRoom['endDate'] = date('Y-m-d H:i', $liveRoom['end_time']);
        $liveRoom['enrollSms'] = $liveRoom['enroll_sms'];
        $liveRoom['enrollWechat'] = $liveRoom['enroll_wechat'];
        $liveRoom['enrollNoticeTime'] = ($liveRoom['start_time'] - $liveRoom['enroll_notice_time']) / 60;
        $liveRoom['isLiveStarted'] = $liveRoom['start_time'] < time();
        $liveRoom['isLiveEnded'] = $liveRoom['end_time'] < time();
        $liveRoom['publish_status'] = empty($liveRoom['is_published']) ? 'unpublish' : 'published';
        $speaker = $this->getUserService()->getUser($liveRoom['speaker']);
        $liveRoom['initSpeaker'] = json_encode(['id' => $speaker['id'], 'name' => $speaker['nickname']]);

        return $liveRoom;
    }

    private function dealLiveDateForListShow($startTime, $endTime)
    {
        if (empty($startTime) || empty($endTime)) {
            return '';
        }
        $startDay = date('Ymd', $startTime);
        $endDay = date('Ymd', $endTime);
        if ($startDay == $endDay) {
            return ['startDay' => date('Y-m-d ', $startTime), 'endMinute' => date('H:i~', $startTime).date('H:i', $endTime)];
        }

        return ['startDay' => date('Y-m-d H:i~', $startTime), 'endMinute' => date('Y-m-d H:i', $endTime)];
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

    private function transLiveRoomForDetail($liveRoom)
    {
        if (empty($liveRoom)) {
            return [];
        }
        $liveRoom['plan_live_time'] = $this->dealLiveDateForListShow($liveRoom['start_time'], $liveRoom['end_time']);
        $liveRoom['actual_student_listen_time'] = $this->transSecondsToHourMinShow($liveRoom['actual_student_listen_time']);
        if (empty($liveRoom['actual_end_time']) || empty($liveRoom['actual_start_time'])) {
            $realLivingSeconds = 0;
        } else {
            $realLivingSeconds = $liveRoom['actual_end_time'] - $liveRoom['actual_start_time'];
        }
        $liveRoom['actual_living_time'] = $this->transSecondsToHourMinShow($realLivingSeconds);

        return $liveRoom;
    }

    private function transLiveOnlineNumRecordsForDetail(array $dataList)
    {
        if (empty($dataList)) {
            $recordsShow = [
                'legend' => ['进入直播人数', '离开直播人数', '在直播间人数'],
                'xAxis' => [date('H:i')],
                'yMax' => 1,
                'series' => [
                    [
                        'name' => '进入直播人数',
                        'type' => 'line',
                        'data' => [0]
                    ],
                    [
                        'name' => '离开直播人数',
                        'type' => 'line',
                        'data' => [0]
                    ],
                    [
                        'name' => '在直播间人数',
                        'type' => 'line',
                        'data' => [0]
                    ]
                ]
            ];
            return $recordsShow;
        }
        $recordsShow = [
            'legend' => ['进入直播人数', '离开直播人数', '在直播间人数'],
            'xAxis' => [],
            'yMax' => 1,
            'series' => []
        ];
        $entryNumLineShow = [
            'name' => '进入直播人数',
            'type' => 'line',
            'data' => []
        ];
        $leaveNumLineShow = [
            'name' => '离开直播人数',
            'type' => 'line',
            'data' => []
        ];
        $totalNumLineShow = [
            'name' => '在直播间人数',
            'type' => 'line',
            'data' => []
        ];
        $yMax = 1;
        foreach ($dataList as $data) {
            $recordsShow['xAxis'][] = date('H:i', $data['save_time']);
            $entryNumLineShow['data'][] = $data['entry_num'];
            $leaveNumLineShow['data'][] = $data['leave_num'];
            $totalNumLineShow['data'][] = $data['total_num'];
            $yMax = max($data['entry_num'], $data['leave_num'], $data['total_num'], $yMax);
        }
        $recordsShow['series'] = [$entryNumLineShow, $leaveNumLineShow, $totalNumLineShow];
        $recordsShow['yMax'] = 1.5 * $yMax;

        return $recordsShow;
    }

    private function transLiveVisitorsForDetail(array $dataList)
    {
        $records = [];
        foreach ($dataList as $data) {
            $tempArr = [];
            $tempArr['user_name'] = $data['user_name'];
            $tempArr['enrolled_time'] = empty($data['enrolled_time']) ? '' : date('Y-m-d H:i:s', $data['enrolled_time']);;
            $tempArr['is_sms_reached'] = empty($data['is_sms_reached']) ? '否' : '是';
            $tempArr['user_avatar'] = $data['user_avatar'];
            $tempArr['user_mobile'] = empty($data['user_mobile']) ? '未知' : $data['user_mobile'];
            $tempArr['actual_listen_time'] = sprintf('%.2f', $data['actual_listen_time']/60);
            $records[] = $tempArr;
        }

        return $records;
    }

    private function transSecondsToHourMinShow($seconds)
    {
        $hourShow = sprintf('%02d', floor($seconds / 3600));
        $minuteShow = sprintf('%02d', floor(($seconds - ($hourShow * 3600)) / 60));
        $secondShow = sprintf('%02d', $seconds - ($hourShow * 3600) - ($minuteShow * 60));

        return $hourShow.':'.$minuteShow.':'.$secondShow;
    }

    private function tryGetSmsSettingWarningMsg()
    {
        $warnMsg = '';
        $smsOverviewUrl = $this->generateUrl('admin_v2_edu_cloud_sms_overview');
        try {
            $cloudSmsSettings = $this->getSettingService()->get('cloud_sms', array());
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/sms/overview');
            $smsInfo = $api->get('/me/sms_account');
            if (empty($smsInfo)) {
                $warnMsg = "云短信还未开通，请先开通云短信，<a href='".$smsOverviewUrl."' style='color:red;text-decoration:underline;' target='_blank'>去开通</a>";
            } else {
                if (empty($smsInfo['name']) && empty($smsInfo['isExistSmsSign'])) {
                    $warnMsg = "还未申请短信签名，<a href='".$smsOverviewUrl."' style='color:red;text-decoration:underline;' target='_blank'>去申请</a>";
                }
                if (empty($smsInfo['name']) && !empty($smsInfo['isExistSmsSign']) && null == $smsInfo['usedSmsSign']) {
                    $warnMsg = "短信签名正在审核中,不能发送短信，<a href='".$smsOverviewUrl."' style='color:red;text-decoration:underline;' target='_blank'>去查看</a>";
                }
            }
        } catch (\Throwable $e) {
            $warnMsg = "尚未启用云短信，<a href='".$smsOverviewUrl."' style='color:red;text-decoration:underline;' target='_blank'>前去开启</a>";
        }
        $isSmsWithoutEnable = (isset($overview['isBuy']) && false == $overview['isBuy']) || (isset($cloudSmsSettings['sms_enabled']) && 0 == $cloudSmsSettings['sms_enabled']) || !isset($cloudSmsSettings['sms_enabled']);
        if ($isSmsWithoutEnable) {
            $warnMsg = "尚未启用云短信，<a href='".$smsOverviewUrl."' style='color:red;text-decoration:underline;' target='_blank'>前去开启</a>";
        }

        return $warnMsg;
    }

    private function transAnalysisDataToFunnelShow($analysisData)
    {
        if (empty($analysisData)) {
            return json_encode([]);
        }
        $showData = [
            ['value' => $analysisData['browsed_num'], 'name' => '访问人数('.number_format($analysisData['browsed_num']).')'],
            ['value' => $analysisData['enrolled_num'], 'name' => '报名人数('.number_format($analysisData['enrolled_num']).')'],
            ['value' => $analysisData['joined_num'], 'name' => '观看人数('.number_format($analysisData['joined_num']).')']
        ];

        return json_encode($showData);
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

    /**
     * @return PluginUserService
     */
    protected function getPluginUserService()
    {
        return $this->createService('OpenLivePlugin:User:PluginUserService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return CashService
     */
    private function getCashService()
    {
        return $this->createService('OpenLivePlugin:Cash:CashService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
