<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\CloudPlatform\Client\CloudAPI;

 class EduSohoUpgrade extends AbstractUpdater
 {
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {

            $this->refreshCopyright();

            $this->getConnection()->commit();
        } catch(\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }
    }

    protected function createAPIClient()
    {
        $settings = $this->createService('System.SettingService')->get('storage', array());
        if (empty($settings['cloud_access_key']) or empty($settings['cloud_secret_key'])) {
            return null;
        }

        return new CloudAPI(array(
            'accessKey' => empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'],
            'secretKey' => empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'],
            'apiUrl' => empty($settings['cloud_api_server']) ? '' : $settings['cloud_api_server'],
        ));
    }

    protected function refreshCopyright()
    {
        $settingService = $this->createService('System.SettingService');

        $api = $this->createAPIClient();
        if (empty($api)) {
            return ;
        }

        $info = $api->get('/me');

        if (isset($info['copyright'])) {
            if ($info['copyright']) {
                $copyright = $settingService->get('copyright', array());
                if (empty($copyright['owned'])) {
                    $copyright['owned'] = 1;
                    $settingService->set('copyright', $copyright);
                }
            } else {
                $settingService->delete('copyright');
            }
        }
    }

 }


 abstract class AbstractUpdater
 {
    protected $kernel;
    public function __construct ($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
   
 }