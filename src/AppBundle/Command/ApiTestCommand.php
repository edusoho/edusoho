<?php

namespace AppBundle\Command;

use Biz\CloudPlatform\Client\FailoverCloudAPI;
use Biz\System\Service\SettingService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Biz\CloudPlatform\Client\CloudAPI;
use Topxia\Service\Common\ServiceKernel;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ApiTestCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('api:test')
            ->addArgument('type', InputArgument::OPTIONAL, '类型');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $type = $input->getArgument('type');
        $type = empty($type) ? 'root' : $type;

        $api = $this->createAPI($type);

        $result = $api->get('/me');
    }

    public static function createAPI($type = 'root')
    {
        /**
         * @var SettingService
         */
        $setting = ServiceKernel::instance()->createService('System:SettingService');

        $storage = $setting->get('storage', array());

        if ($type == 'root') {
            $api = new CloudAPI(array(
                'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
                'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
                'apiUrl' => empty($storage['cloud_api_server']) ? '' : $storage['cloud_api_server'],
                'debug' => true,
            ));
        } else {
            $api = new FailoverCloudAPI(array(
                'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
                'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
                'apiUrl' => empty($storage['cloud_api_server']) ? '' : $storage['cloud_api_server'],
                'debug' => true,
            ));

            $serverConfigFile = ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/api_server_test.json';
            $api->setApiServerConfigPath($serverConfigFile);
            $api->setApiType($type);
        }

        $logger = new Logger('CloudAPI');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/cloud-api_test.log', Logger::DEBUG));
        $api->setLogger($logger);

        return $api;
    }
}
