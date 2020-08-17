<?php

namespace AppBundle\Controller\Certificate;

use AppBundle\Controller\BaseController;
use Biz\Certificate\Service\RecordService;
use Symfony\Component\HttpFoundation\Request;

class CertificateController extends BaseController
{
    public function generateImageAction(Request $request, $id)
    {
        $record = $this->getRecordService()->get($id);
        if (empty($record)) {
            return $this->createJsonResponse('');
        }

        $img = $this->getCertificateStrategy($record['targetType'])->getCertificateImg($record);

        return $this->createJsonResponse($img);
    }

    protected function getCertificateStrategy($type)
    {
        return $this->getBiz()->offsetGet('certificate.strategy_context')->createStrategy($type);
    }

    /**
     * @return RecordService
     */
    protected function getRecordService()
    {
        return $this->createService('Certificate:RecordService');
    }
}
