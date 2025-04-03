<?php

namespace AppBundle\Controller\Export;

use AppBundle\Common\FileToolkit;
use AppBundle\Common\SmsToolkit;
use AppBundle\Controller\BaseController;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class ExportController extends BaseController
{
    public function tryExportAction(Request $request, $name, $limit)
    {
        $conditions = $request->query->all();
        if ('order' == $name) {
            $request->request->set('sms_code', $request->query->get('sms_code'));
            $request->request->set('mobile', $request->query->get('mobile'));
            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, 'sms_secondary_verification');
            if (!$result) {
                $response = ['success' => 0, 'message' => 'export.not_allowed'];
            }
            if (!$this->getCurrentUser()->hasPermission('custom_export_permission')) {
                $response = ['success' => 0, 'message' => 'export.not_allowed'];
            }
        }

        $names = $request->query->get('names', [$name]);

        try {
            $batchExporter = $this->container->get('batch_exporter');
        } catch (\Exception $e) {
            return $this->createJsonResponse(['message' => $e->getMessage()]);
        }

        $batchExporter->findExporter($names, $conditions);
        if (!$batchExporter->canExport()) {
            $response = ['success' => 0, 'message' => 'export.not_allowed'];
        }

        $counts = $batchExporter->getCount();
        if (0 == count($counts)) {
            $response = ['success' => 0, 'message' => 'export.empty'];
        }

        $magic = $this->getSettingService()->get('magic');
        if (empty($magic['export_allow_count'])) {
            $magic['export_allow_count'] = 10000;
        }

        if (max($counts) > $magic['export_allow_count'] && !empty($limit)) {
            $response = [
                'success' => 0,
                'message' => 'export.over.limit',
                'parameters' => ['exportAllowCount' => $magic['export_allow_count'], 'count' => max($counts)],
            ];
        }
        $response = $response ?? ['success' => 1, 'counts' => $counts];

        return $this->createJsonResponse($response);
    }

    public function preExportAction(Request $request, $name)
    {
        $conditions = $request->query->all();
        $names = $request->query->get('names', [$name]);
        $currentName = $request->query->get('name', $name);

        $batchExporter = $this->container->get('batch_exporter');
        $batchExporter->findExporter($names, $conditions);
        $result = $batchExporter->export($currentName);

        return $this->createJsonResponse($result);
    }

    public function exportAction(Request $request, $name, $type)
    {
        if (!$this->getCurrentUser()->isAdmin() && !$this->getCurrentUser()->isTeacher()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }
        $fileNames = $request->query->get('fileNames');
        $customFileName = $request->query->get('customFileName');
        $batchExporter = $this->container->get('batch_exporter');
        $batchExporter->findExporter([$name], []);
        list($path, $name) = $batchExporter->exportFile($name, $fileNames, $customFileName);

        if (!file_exists($path)) {
            return $this->createJsonResponse(['success' => 0, 'message' => 'empty file']);
        }

        $filenameParts = explode('.', $name);
        $ext = array_pop($filenameParts);

        $headers = [
            'Content-Type' => FileToolkit::getMimeTypeByExtension($ext),
            'Content-Disposition' => 'attachment; filename='.$name,
        ];
        $batchExporter->postExport();

        return new BinaryFileResponse($path, 200, $headers);
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
