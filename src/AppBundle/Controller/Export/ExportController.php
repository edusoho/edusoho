<?php

namespace AppBundle\Controller\Export;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\ExportToolkit;

class ExportController extends BaseController
{
    public function tryExportAction(Request $request, $name)
    {
        $conditions = $request->query->all();
        $exportLimit = $request->query->get('g');

        $export = $this->container->get('export_factory')->create($name, $conditions);
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

        if ($count > $magic['export_allow_count'] && !empty($exportLimit)) {
            $response = array(
                'success' => 0,
                'message' => 'export.over.limit',
                'parameters' => array('exportAllowCount' => $magic['export_allow_count'], 'count' => $count),
            );
        }

        return $this->createJsonResponse($response);
    }

    public function exportAction(Request $request, $fileName, $type)
    {
        $officeHelpMap = array(
            'csv' => 'AppBundle\Component\Office\CsvHelper'
        );
        $officeHelp =new $officeHelpMap[$type];
        $filePath = $request->query->get('filePath');

        if (empty($filePath)) {
            return  $this->createJsonResponse(array('success' => 0, 'message' => 'filePath is empty'));
        }

        return $officeHelp->write($fileName, $filePath);
    }

    public function preExportAction(Request $request, $name)
    {
        $conditions = $request->query->all();
        try {
            $exporter = $this->container->get('export_factory')->create($name, $conditions);

            $result = $exporter->export($name);
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('error' => $e->getMessage()));
        }

        return $this->createJsonResponse($result);
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
