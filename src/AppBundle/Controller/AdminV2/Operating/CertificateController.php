<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\TemplateService;
use Symfony\Component\HttpFoundation\Request;

class CertificateController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->request->all();

        $paginator = new Paginator(
            $request,
            $this->getCertificateService()->count($conditions),
            20
        );

        $certificates = $this->getCertificateService()->search(
            $conditions,
            ['createdTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/operating/certificate/index.html.twig', [
            'certificates' => $certificates,
            'paginator' => $paginator,
        ]);
    }

    public function createAction(Request $request)
    {
        $data = $request->request->all();

        if ($request->isMethod('POST') && empty($data['back'])) {
            return $this->redirect($this->generateUrl('admin_v2_certificate_create_detail'));
        }

        return $this->render('admin-v2/operating/certificate/manage/create-base-info.html.twig', [
            'certificate' => $data,
        ]);
    }

    public function createDetailAction()
    {
    }

    public function editAction(Request $request, $id)
    {
    }

    public function closeAction(Request $request, $id)
    {
    }

    public function publishAction(Request $request, $id)
    {
    }

    public function memberListAction(Request $request, $id)
    {
    }

    public function auditManageAction(Request $request, $id)
    {
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->createService('Certificate:CertificateService');
    }

    /**
     * @return TemplateService
     */
    protected function getCertificateTemplateService()
    {
        return $this->createService('Certificate:TemplateService');
    }
}
