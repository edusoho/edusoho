<?php

namespace AppBundle\Controller\Export;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class ExportController extends BaseController
{
    public function tryExportAction(Request $request, $name, $limit)
    {
        $conditions = $request->query->all();

        try {
            $export = $this->container->get('export_factory')->create($name, $conditions);
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('message' => $e->getMessage()));
        }
        $response = array('success' => 1);

        $count = $export->getCount();

        if (!$export->canExport()) {
            $response = array('success' => 0, 'message' => 'export.not_allowed');
        }

        $magic = $this->getSettingService()->get('magic');

        if (0 == $count) {
            $response = array('success' => 0, 'message' => 'export.empty');
        }

        if (empty($magic['export_allow_count'])) {
            $magic['export_allow_count'] = 10000;
        }

        if ($count > $magic['export_allow_count'] && !empty($limit)) {
            $response = array(
                'success' => 0,
                'message' => 'export.over.limit',
                'parameters' => array('exportAllowCount' => $magic['export_allow_count'], 'count' => $count),
            );
        }

        return $this->createJsonResponse($response);
    }

    public function preExportAction(Request $request, $name)
    {
        $conditions = $request->query->all();

        $exporter = $this->container->get('export_factory')->create($name, $conditions);
        $result = $exporter->export($name);

        return $this->createJsonResponse($result);
    }

    public function exportAction(Request $request, $name, $type)
    {
        $biz = $this->getBiz();
        $fileName = $request->query->get('fileName');

        $exportPath = $biz['topxia.upload.private_directory'].'/'.basename($fileName);
        if (!file_exists($exportPath)) {
            return  $this->createJsonResponse(array('success' => 0, 'message' => 'empty file'));
        }

        $officeHelpMap = array(
            'csv' => 'AppBundle\Component\Office\CsvHelper',
        );
        $officeHelp = new $officeHelpMap[$type]();

        return $officeHelp->write($name, $exportPath);
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
