<?php

namespace AppBundle\Controller\Certificate;

use AppBundle\Controller\BaseController;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

    public function certificateRecordAction(Request $request, $recordId)
    {
        $record = $this->getRecordService()->get($recordId);
        if (empty($record)) {
            return $this->createMessageResponse('error', '证书不存在！');
        }

        $user = $this->getUserService()->getUserAndProfile($record['userId']);
        $certificate = $this->getCertificateService()->get($record['certificateId']);

        return $this->render('certificate/certificate-record.html.twig', [
            'record' => $record,
            'user' => $user,
            'url' => $this->generateUrl('certificate_record', ['recordId' => $recordId], UrlGeneratorInterface::ABSOLUTE_URL),
            'certificate' => $certificate,
        ]);
    }

    public function certificateImageDownloadAction(Request $request, $recordId)
    {
        $record = $this->getRecordService()->get($recordId);
        $img = $this->getCertificateStrategy($record['targetType'])->getCertificateImg($record);
        $certificate = $this->getCertificateService()->get($record['certificateId']);

        return new Response(base64_decode($img), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="'.$certificate['name'].'.png"',
        ]);
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

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->createService('Certificate:CertificateService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
