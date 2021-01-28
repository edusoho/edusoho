<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class PageSettingController extends BaseController
{
    public function performanceAction(Request $request)
    {
        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            $this->setFlashMessage('success', 'site.save.success');
            $this->getSettingService()->set('performance', $data);

            return $this->redirect($this->generateUrl('admin_v2_performance'));
        }

        return $this->render('admin-v2/developer/page-static/performance-setting.html.twig');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
