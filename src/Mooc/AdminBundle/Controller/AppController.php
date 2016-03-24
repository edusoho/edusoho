<?php

namespace Mooc\AdminBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\AppController as BaseAppController;


class AppController extends BaseAppController
{

    public function installedAction(Request $request, $postStatus)
    {
        $apps = $this->getAppService()->getCenterApps() ?: array();

        $apps = ArrayToolkit::index($apps, 'code');

        $appsInstalled = $this->getAppService()->findApps(0, 100);
        $appsInstalled = ArrayToolkit::index($appsInstalled, 'code');

        $dir     = dirname(dirname(dirname(dirname(__DIR__))));
        $appMeta = array();

        foreach ($apps as $key => $value) {
            unset($apps[$key]);

            $appInfo = $value;
            $code    = strtolower($key);

            $apps[$code] = $appInfo;
        }

        foreach ($appsInstalled as $key => $value) {
            $appItem = $key;
            unset($appsInstalled[$key]);

            $appInfo = $value;
            $key     = strtolower($key);

            $appsInstalled[$key]              = $appInfo;
            $appsInstalled[$key]['installed'] = 1;

            $appsInstalled[$key]['id'] = isset($apps[$key]) ? $apps[$key]['id'] : $appsInstalled[$key]['id'];

            $appsInstalled[$key]['icon'] = !empty($apps[$key]['icon']) ? $apps[$key]['icon'] : null;

            if ($key != 'MOOCMAIN') {
                if (in_array($key, array("vip", "coupon"))) {
                    $key = ucfirst($appItem);
                } else {
                    $key = $appItem;
                }

                $dic = $dir . '/plugins/' . $key . '/plugin.json';

                if (file_exists($dic)) {
                    $appMeta[$appItem] = json_decode(file_get_contents($dic));
                }
            }
        }

        $apps = array_merge($apps, $appsInstalled);

        $theme  = array();
        $plugin = array();

        foreach ($apps as $key => $value) {
            if ($value['type'] == 'theme') {
                $theme[] = $value;
            } elseif ($value['type'] == 'plugin' || $value['type'] == 'app') {
                $plugin[] = $value;
            }
        }

        return $this->render('TopxiaAdminBundle:App:installed.html.twig', array(
            'apps'    => $apps,
            'theme'   => $theme,
            'plugin'  => $plugin,
            'type'    => $postStatus,
            'appMeta' => $appMeta
        ));
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
