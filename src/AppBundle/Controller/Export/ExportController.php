<?php

namespace AppBundle\Controller\Export;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class ExportController extends BaseController
{
    public function tryExportAction(Request $request, $name, $limit)
    {
        $conditions = $request->query->all();
        $names = $request->query->get('names', [$name]);

        try {
            $batchExporter = $this->container->get('batch_exporter');
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('message' => $e->getMessage()));
        }
        $response = array('success' => 1);

        $batchExporter->findExporter($names, $conditions);
        $counts = $batchExporter->getCount();

        if (!$batchExporter->canExport()) {
            $response = array('success' => 0, 'message' => 'export.not_allowed');
        }

        $magic = $this->getSettingService()->get('magic');

        if (0 == count($counts)) {
            $response = array('success' => 0, 'message' => 'export.empty');
        }

        if (empty($magic['export_allow_count'])) {
            $magic['export_allow_count'] = 10000;
        }

        if (max($counts) > $magic['export_allow_count'] && !empty($limit)) {
            $response = array(
                'success' => 0,
                'message' => 'export.over.limit',
                'parameters' => array('exportAllowCount' => $magic['export_allow_count'], 'count' => max($counts)),
            );
        }

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
        $fileNames = $request->query->get('fileNames');

        return $this->container->get('batch_exporter')->exportFile($name, $fileNames);
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
