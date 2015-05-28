<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Topxia\Service\Util\LiveClientFactory;
use Topxia\Service\Util\CloudClientFactory;

class DeveloperSettingController extends BaseController
{
    public function indexAction(Request $request)
    {
        $developerSetting = $this->getSettingService()->get('developer', array());
        $storageSetting = $this->getSettingService()->get('storage', array());

        $default = array(
            'debug' => '0',
            'app_api_url' => '',
            'cloud_api_server' => empty($storageSetting['cloud_api_server']) ? '' : $storageSetting['cloud_api_server'],
            'hls_encrypted' => '1',
        );

        $developerSetting = array_merge($default, $developerSetting);

        if ($request->getMethod() == 'POST') {
            $developerSetting = $request->request->all();
            $storageSetting['cloud_api_server'] = $developerSetting['cloud_api_server'];
            $this->getSettingService()->set('storage', $storageSetting);
            $this->getSettingService()->set('developer', $developerSetting);

            $this->getLogService()->info('system', 'update_settings', "更新开发者设置", $developerSetting);
            $this->setFlashMessage('success', '开发者已保存！');
        }

        return $this->render('TopxiaAdminBundle:DeveloperSetting:index.html.twig', array(
            'developerSetting' => $developerSetting,
        ));

    }

    public function versionAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $app = $this->getAppservice()->getAppByCode($data['code']);
            if (empty($app)) {
                throw $this->createNotFoundException();
            }
            $this->getAppservice()->updateAppVersion($app['id'], $data['version']);
            return $this->redirect($this->generateUrl('admin_app_upgrades'));
        }

        $appCount = $this->getAppservice()->findAppCount();
        $apps = $this->getAppservice()->findApps(0, $appCount);

        return $this->render('TopxiaAdminBundle:DeveloperSetting:version.html.twig', array(
            'apps' => $apps,
        ));
    }

    public function magicAction(Request $request)
    {

        if ($request->getMethod() == 'POST') {
            $setting = $request->request->get('setting', '{}');
            $setting = json_decode($setting, true);
            $this->getSettingService()->set('magic', $setting);
            $this->getLogService()->info('system', 'update_settings', "更新Magic设置", $setting);
            $this->setFlashMessage('success', '设置已保存！');
        }

        $setting = $this->getSettingService()->get('magic', array());
        $setting = $this->prettyPrint(json_encode($setting));

        return $this->render('TopxiaAdminBundle:DeveloperSetting:magic.html.twig', array(
            'setting' => $setting,
        ));
    }

    protected function prettyPrint( $json )
    {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = NULL;
        $json_length = strlen( $json );

        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if ( $in_escape ) {
                $in_escape = false;
            } else if( $char === '"' ) {
                $in_quotes = !$in_quotes;
            } else if( ! $in_quotes ) {
                switch( $char ) {
                    case '}': case ']':
                        $level--;
                        $ends_line_level = NULL;
                        $new_line_level = $level;
                        break;

                    case '{': case '[':
                        $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ": case "\t": case "\n": case "\r":
                        $char = "";
                        $ends_line_level = $new_line_level;
                        $new_line_level = NULL;
                        break;
                }
            } else if ( $char === '\\' ) {
                $in_escape = true;
            }
            if( $new_line_level !== NULL ) {
                $result .= "\n".str_repeat( "\t", $new_line_level );
            }
            $result .= $char.$post;
        }

        return $result;
    }


    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }
}