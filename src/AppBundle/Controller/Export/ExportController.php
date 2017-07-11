<?php

namespace AppBundle\Controller\Export;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use AppBundle\Common\FileToolkit;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends BaseController
{
    public function exportAction(Request $request, $fileName)
    {
        $fileName = sprintf($fileName.'-(%s).csv', date('Y-n-d'));
        $filePath = $request->query->get('filePath');

        $str = file_get_contents($filePath);
        if (!empty($filePath)) {
            FileToolkit::remove($filePath);
        }

        $str = chr(239).chr(187).chr(191).$str;

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;

    }

    public function preExportAction(Request $request, $fileName)
    {
        $conditions = $request->query->all();
        try {
            $export = $this->getExport($conditions, $fileName);
            if (!$export->canExport()) {
                return $this->createJsonResponse(array('error' => 'you are not allowed to download'));
            }
            $result = $export->getPreResult($fileName);
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('error' => $e->getMessage()));
        }
        return $this->createJsonResponse($result);
    }

    private function getExport($conditions, $name)
    {
        $map = array(
            'invite-records' => 'Biz\Export\inviteRecordsExport',
        );

        return new $map[$name]($this->getBiz(), $conditions);
    }
}
