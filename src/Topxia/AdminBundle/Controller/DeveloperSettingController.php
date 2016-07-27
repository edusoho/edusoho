<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\Common\JsonToolkit;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DeveloperSettingController extends BaseController
{
    public function indexAction(Request $request)
    {
        $developerSetting = $this->getSettingService()->get('developer', array());
        $storageSetting   = $this->getSettingService()->get('storage', array());

        $default = array(
            'debug'                  => '0',
            'without_network'        => '0',
            'cloud_api_server'       => empty($storageSetting['cloud_api_server']) ? '' : $storageSetting['cloud_api_server'],
            'cloud_file_server'      => '',
            'cloud_api_tui_server'   => empty($storageSetting['cloud_api_tui_server']) ? '' : $storageSetting['cloud_api_tui_server'],
            'cloud_api_event_server' => empty($storageSetting['cloud_api_event_server']) ? '' : $storageSetting['cloud_api_event_server'],
            'app_api_url'            => '',
            'cloud_sdk_cdn'          => '',
            'hls_encrypted'          => '1'
        );

        $developerSetting = array_merge($default, $developerSetting);

        if ($request->getMethod() == 'POST') {
            $developerSetting = $request->request->all();

            $storageSetting['cloud_api_server']       = $developerSetting['cloud_api_server'];
            $storageSetting['cloud_api_tui_server']   = $developerSetting['cloud_api_tui_server'];
            $storageSetting['cloud_api_event_server'] = $developerSetting['cloud_api_event_server'];
            $this->getSettingService()->set('storage', $storageSetting);
            $this->getSettingService()->set('developer', $developerSetting);

            $this->getLogService()->info('system', 'update_settings', "更新开发者设置", $developerSetting);

            $this->dealServerConfigFile();

            $this->setFlashMessage('success', '开发者已保存！');
        }

        return $this->render('TopxiaAdminBundle:DeveloperSetting:index.html.twig', array(
            'developerSetting' => $developerSetting
        ));
    }

    protected function dealServerConfigFile()
    {
        $serverConfigFile = $this->getServiceKernel()->getParameter('kernel.root_dir').'/data/api_server.json';
        $fileSystem       = new Filesystem();
        $fileSystem->remove($serverConfigFile);
    }

    public function versionAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $app  = $this->getAppservice()->getAppByCode($data['code']);

            if (empty($app)) {
                throw $this->createNotFoundException();
            }

            $this->getAppservice()->updateAppVersion($app['id'], $data['version']);
            return $this->redirect($this->generateUrl('admin_app_upgrades'));
        }

        $appCount = $this->getAppservice()->findAppCount();
        $apps     = $this->getAppservice()->findApps(0, $appCount);

        return $this->render('TopxiaAdminBundle:DeveloperSetting:version.html.twig', array(
            'apps' => $apps
        ));
    }

    public function magicAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $setting = $request->request->get('setting', '{}');
            $setting = json_decode($setting, true);

            if (empty($setting)) {
                $setting = array('export_allow_count' => 100000, 'export_limit' => 10000, 'enable_org' => 0);
            }

            $this->getSettingService()->set('magic', $setting);
            $this->getLogService()->info('system', 'update_settings', "更新Magic设置", $setting);
            $this->setFlashMessage('success', '设置已保存！');
        }

        $setting = $this->getSettingService()->get('magic', array());
        $setting = JsonToolkit::prettyPrint(json_encode($setting));

        return $this->render('TopxiaAdminBundle:DeveloperSetting:magic.html.twig', array(
            'setting' => $setting
        ));
    }

    public function redisAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $redis            = $request->request->all();
            $redis['setting'] = json_decode($redis['setting'], true);
            $this->getSettingService()->set('redis', $redis);

            $redisConfigFile = $this->container->getParameter('kernel.root_dir').'/data/redis.php';

            if ($redis['opened'] == '1') {
                $config = "<?php \nreturn ".var_export($redis['setting'], true).';';
                file_put_contents($redisConfigFile, $config);
            }

            if ($redis['opened'] == '0') {
                file_exists($redisConfigFile) && unlink($redisConfigFile);
            }

            $this->getLogService()->info('system', 'update_redis', "更新redis设置", $redis);
            $this->setFlashMessage('success', '设置已保存！');
        }

        $redis = $this->getSettingService()->get('redis', array());

        if (isset($redis['setting']) && !empty($redis['setting'])) {
            $redis['setting'] = JsonToolkit::prettyPrint(json_encode($redis['setting']));
        } else {
            $redis['setting'] = '{}';
        }

        return $this->render('TopxiaAdminBundle:DeveloperSetting:redis.html.twig', array(
            'redis' => $redis
        ));
    }

    public function syncUploadFileAction(Request $request)
    {
        $conditions = array(
            'storage'  => 'cloud',
            'globalId' => 0
        );
        $this->getCloudFileService()->synData($conditions);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getCloudFileService()
    {
        return $this->getServiceKernel()->createService('CloudFile.CloudFileService');
    }
}
