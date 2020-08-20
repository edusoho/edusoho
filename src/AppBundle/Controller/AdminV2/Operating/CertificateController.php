<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\TemplateService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class CertificateController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

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

        if (empty($data['back'])) {
            $certificate = $this->getCertificateService()->create($data);

            return $this->redirect($this->generateUrl('admin_v2_certificate_manage'));
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
        $certificate = $this->getCertificateService()->get($id);
        if (empty($certificate)) {
            $this->createNewException(CertificateException::NOTFOUND_CERTIFICATE());
        }

        if ($request->isMethod('POST')) {
            $fields = $request->request->all();
            $this->getCertificateService()->update($id, $fields);

            return $this->redirect($this->generateUrl('admin_v2_certificate_manage'));
        }

        $strategy = $this->getBiz()->offsetGet('certificate.strategy_context')->createStrategy($certificate['targetType']);
        $template = $this->getCertificateTemplateService()->get($certificate['templateId']);

        return $this->render('admin-v2/operating/certificate/manage/update.html.twig', [
            'certificate' => $certificate,
            'target' => $strategy->getTarget($certificate['targetId']),
            'template' => $template,
        ]);
    }

    public function closeAction(Request $request, $id)
    {
        $this->getCertificateService()->closeCertificate($id);

        return $this->createJsonResponse(true);
    }

    public function publishAction(Request $request, $id)
    {
        $this->getCertificateService()->publishCertificate($id);

        return $this->createJsonResponse(true);
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getCertificateService()->delete($id);

        return $this->createJsonResponse(true);
    }

    public function auditManageAction(Request $request, $id)
    {
    }

    public function targetModalAction(Request $request)
    {
        return $this->render('admin-v2/operating/certificate/target/base-modal.html.twig', [
            'targetType' => $request->query->get('targetType'),
        ]);
    }

    public function targetSearchAction(Request $request, $type)
    {
        $conditions = $request->query->all();

        $strategy = $this->getBiz()->offsetGet('certificate.strategy_context')->createStrategy($type);

        $paginator = new Paginator(
            $request,
            $strategy->count($conditions),
            5
        );
        $paginator->setBaseUrl($this->generateUrl('admin_v2_certificate_target_search', array_merge(['type' => $type], $request->query->all())));

        $targets = $strategy->search(
            $conditions,
            ['updatedTime' => 'desc'],
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
        return $this->render('admin-v2/operating/certificate/template/base-modal.html.twig', [
            'targetType' => $request->query->get('targetType'),
        ]);
    }

    public function templateSearchAction(Request $request, $type)
    {
        $conditions = $request->query->all();
        $conditions['targetType'] = $type;

        $paginator = new Paginator(
            $request,
            $this->getCertificateTemplateService()->count($conditions),
            5
        );

        $templates = $this->getCertificateTemplateService()->search(
            $conditions,
            ['updatedTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($templates, 'createdUserId'));

        return $this->render('admin-v2/operating/certificate/template/template-modal.html.twig', [
            'templates' => $templates,
            'paginator' => $paginator,
            'users' => ArrayToolkit::index($users, 'id'),
        ]);
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
