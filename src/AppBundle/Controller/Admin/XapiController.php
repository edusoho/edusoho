<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use Biz\System\Service\SettingService;
use Biz\Xapi\Service\XapiService;
use Symfony\Component\HttpFoundation\Request;

class XapiController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $count = $this->getXapiService()->countStatements($conditions);

        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $statements = $this->getXapiService()->searchStatements(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'admin/xapi/list.html.twig',
            array(
                'statements' => $statements,
                'paginator' => $paginator,
            )
        );
    }

    public function detailAction(Request $request, $id)
    {
        $statement = $this->getXapiService()->getStatement($id);

        return $this->render(
            'admin/xapi/detail.html.twig',
            array(
                'statement' => $statement,
            )
        );
    }

    public function settingAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $xapiSetting = $request->request->all();
            $default = array(
                'enabled' => 0,
                'push_url' => 'https://lrs.qiqiuyun.net/v1/xapi/',
            );
            $xapiSetting = array_merge($default, $xapiSetting);
            $this->getSettingService()->set('xapi', $xapiSetting);
            $this->getLogService()->info('xapi', 'update_setting', 'xapi.update_setting.success', $xapiSetting);
            $this->setFlashMessage('success', 'xAPI设置已保存');
        }

        $xapiSetting = $this->getSettingService()->get('xapi', array());

        return $this->render(
            'admin/xapi/setting.html.twig',
            array(
                'xapiSetting' => $xapiSetting,
            )
        );
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->createService('Xapi:XapiService');
    }
}
