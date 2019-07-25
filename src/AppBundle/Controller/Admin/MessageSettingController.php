<?php

namespace AppBundle\Controller\Admin;

use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class MessageSettingController extends BaseController
{
    public function messageSettingAction(Request $request)
    {
        $message = $this->getSettingService()->get('message', array());

        $default = array(
            'showable' => '1',
        );

        $message = array_merge($default, $message);

        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            $message = array_merge($message, $set);

            $this->getSettingService()->set('message', $set);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin/message/set.html.twig', array(
            'messageSetting' => $message,
        ));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
