<?php

namespace OpenLivePlugin\Controller\Admin;

use OpenLivePlugin\Biz\OpenLiveManage\Service\OpenLiveManageService;
use Symfony\Component\HttpFoundation\Request;

class SmsReachedStudentExportController extends ExportController
{
    public function tryExportAction(Request $request, $id)
    {
        $conditions = array_merge($request->query->all(), ['offset' => 0, 'limit' => 1]);
        if (!empty($conditions['keyword'])) {
            $conditions['mobile'] = $conditions['keyword'];
        }
        if (!$this->canExport($id)) {
            $response = ['success' => 0, 'message' => 'export.not_allowed'];

            return $this->createJsonResponse($response);
        }
        $smsReachedStudents = $this->getOpenLiveManageService()->searchSmsReachedRoomStudent($id, $conditions);
        $smsReachedStudentCount = empty($smsReachedStudents['data']) ? 0 : $smsReachedStudents['paging']['total'];

        $response = ['success' => 1];
        if (0 == $smsReachedStudentCount) {
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
        if (!empty($conditions['keyword']) && !empty($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
        }
        $start = isset($conditions['start']) ? $conditions['start'] : 0;
        $limit = 500;

        $filePath = $this->exportFileRootPath().$fileName;

        $smsReachedStudentData = $this->getOpenLiveManageService()->searchSmsReachedRoomStudent($id, array_merge($conditions, ['offset' => $start, 'limit' => $limit]));
        $smsReachedStudents = empty($smsReachedStudentData['data']) ? [] : $smsReachedStudentData['data'];
        $smsReachedStudentCount = empty($smsReachedStudentData['data']) ? 0 : $smsReachedStudentData['paging']['total'];
        $data = $this->transContent($smsReachedStudents);
        $this->addContent($data, $start, $filePath);

        $endPage = $start + $limit;
        $endStatus = $endPage >= $smsReachedStudentCount;
        $status = $endStatus ? 'finish' : 'continue';

        return $this->createJsonResponse([
            'status' => $status,
            'fileName' => $fileName,
            'start' => $endPage,
            'count' => $smsReachedStudentCount,
            'success' => '1',
        ]);
    }

    protected function canExport($liveId)
    {
        $user = $this->getUser();

        return $user->isAdmin();
    }

    private function transContent($dataList)
    {
        $statisticsContent = [];
        foreach ($dataList as $data) {
            $tempData = [];
            $tempData[] = $data['nickname'];
            $tempData[] = $data['mobile'];
            $tempData[] = empty($data['enrolled_time']) ? '--' : date('Y-m-d H:i:s', $data['enrolled_time']);

            $statisticsContent[] = $tempData;
        }

        return $statisticsContent;
    }

    protected function transTitles()
    {
        return ['用户昵称', '手机号', '报名时间'];
    }
}
