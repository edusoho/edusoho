<?php

namespace OpenLivePlugin\Controller\Admin;

use OpenLivePlugin\Biz\OpenLiveManage\Service\OpenLiveManageService;
use Symfony\Component\HttpFoundation\Request;

class LiveVisitorExportController extends ExportController
{
    public function tryExportAction(Request $request, $id)
    {
        $conditions = array_merge($request->query->all(), ['offset' => 0, 'limit' => 1]);
        if (!$this->canExport($id)) {
            $response = ['success' => 0, 'message' => 'export.not_allowed'];

            return $this->createJsonResponse($response);
        }
        $liveVisitorReports = $this->getOpenLiveManageService()->getLiveVisitorReport($id, $conditions);
        $liveVisitorCount = empty($liveVisitorReports['data']) ? 0 : $liveVisitorReports['paging']['total'];

        $response = ['success' => 1];
        if (0 == $liveVisitorCount) {
            $response = array('success' => 0, 'message' => 'export.empty');
        }

        return $this->createJsonResponse($response);
    }

    public function preExportAction(Request $request, $id, $fileName)
    {
        if (!$this->canExport($id)) {
            return array(
                'success' => 0,
                'message' => 'export.not_allowed',
            );
        }
        $conditions = $request->query->all();
        $start = isset($conditions['start']) ? $conditions['start'] : 0;
        $limit = 500;

        $filePath = $this->exportFileRootPath().$fileName;

        $liveVisitorReports = $this->getOpenLiveManageService()->getLiveVisitorReport($id, array_merge($conditions, ['offset' => $start, 'limit' => $limit]));
        $liveVisitors = empty($liveVisitorReports['data']) ? [] : $liveVisitorReports['data'];
        $liveVisitorCount = empty($liveVisitorReports['data']) ? 0 : $liveVisitorReports['paging']['total'];
        $data = $this->transContent($liveVisitors);
        $this->addContent($data, $start, $filePath);

        $endPage = $start + $limit;
        $endStatus = $endPage >= $liveVisitorCount;
        $status = $endStatus ? 'finish' : 'continue';

        return $this->createJsonResponse([
            'status' => $status,
            'fileName' => $fileName,
            'start' => $endPage,
            'count' => $liveVisitorCount,
            'success' => '1',
        ]);
    }

    protected function canExport($liveId)
    {
        $this->getOpenLiveManageService()->checkLiveRoomDetailAccess($liveId);
        $user = $this->getUser();

        return $user->isAdmin();
    }

    private function transContent($dataList)
    {
        $statisticsContent = [];
        foreach ($dataList as $data) {
            $tempData = [];
            $tempData[] = $data['user_name'];
            $tempData[] = $data['user_mobile'];
            $tempData[] = empty($data['enrolled_time']) ? '' : date('Y-m-d H:I:s', $data['enrolled_time']);
            $tempData[] = empty($data['is_sms_reached']) ? '否' : '是';
            $tempData[] = sprintf("%.2f", $data['actual_listen_time'] / 60);

            $statisticsContent[] = $tempData;
        }

        return $statisticsContent;
    }

    protected function transTitles()
    {
        return ['用户昵称', '手机号', '报名时间', '短信通知是否已达', '累计观看时长(分钟)'];
    }
}
