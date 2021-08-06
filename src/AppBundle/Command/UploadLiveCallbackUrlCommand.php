<?php

namespace AppBundle\Command;

use Biz\Util\EdusohoLiveClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;

class UploadLiveCallbackUrlCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:upload-live-url')
            ->addArgument('host', InputArgument::REQUIRED, '域名')
            ->setDescription('上传直播回调路由');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getArgument('host');
        $client = new EdusohoLiveClient();

        $result = $client->uploadCallbackUrl($host.'/callback/live/handle');
        var_dump($result);
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
