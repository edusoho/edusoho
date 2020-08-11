<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\TemplateService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\User\Service\UserService;
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

    public function createDetailAction(Request $request)
    {
        $data = $request->request->all();
        if (empty($data)) {
            return $this->redirect($this->generateUrl('admin_v2_certificate_create'));
        }

        return $this->render('admin-v2/operating/certificate/manage/create-detail.html.twig', [
            'certificate' => $data,
        ]);
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

    public function targetModalAction(Request $request)
    {
        return $this->render('admin-v2/operating/certificate/target/base-modal.html.twig', [
            'targetType' => $request->request->get('targetType'),
        ]);
    }

    public function targetSearchAction(Request $request, $type)
    {
        $conditions = $request->query->all();

        $strategy = $this->getBiz()->offsetGet('certificate.strategy_context')->createStrategy($type);

        $paginator = new Paginator(
            $request,
            $strategy->count($conditions),
            20
        );

        $targets = $strategy->search(
            $conditions,
            ['createdTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($targets, 'categoryId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($targets, 'creator'));

        return $this->render($strategy->getTargetModal(), [
            'targets' => $targets,
            'paginator' => $paginator,
            'categories' => ArrayToolkit::index($categories, 'id'),
            'users' => ArrayToolkit::index($users, 'id'),
        ]);
    }

    public function templateModalAction(Request $request)
    {
    }

    public function codeCheckAction(Request $request)
    {
        $code = $request->query->get('value', '');
        if (empty($code)) {
            return $this->createJsonResponse(true);
        }
        $certificate = $this->getCertificateService()->getCertificateByCode($code);
        if (!empty($certificate)) {
            return $this->createJsonResponse(false);
        }

        return $this->createJsonResponse(true);
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

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
