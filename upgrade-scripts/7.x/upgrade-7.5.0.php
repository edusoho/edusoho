<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->updateScheme();

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();
        $block = $this->getBlockService()->getBlockTemplateByCode('cloud_search_banner');
        if (empty($block)) {
            $connection->exec("INSERT INTO `block_template` 
            (`title`, `mode`, `template`, `templateName`, `templateData`, `content`, `data`, `code`, `meta`, `tips`, `category`, `createdTime`, `updateTime`)
            VALUES (
            '云搜索背景图',
            'template',
            NULL, 
            'TopxiaWebBundle:Block:cloud_search_banner.template.html.twig',
            NULL,
            '', 
            '{\"posters\":[{\"src\":\"\\/assets\\/img\\/placeholder\\/banner_search.jpg\",\"alt\":\"背景图\",\"layout\":\"tile\",\"background\":\"#2b9cf0\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"}]}',
            'cloud_search_banner', 
            '{\"title\":\"背景图\",\"category\":\"system\",\"templateName\":\"TopxiaWebBundle:Block:cloud_search_banner.template.html.twig\",\"items\":{\"posters\":{\"title\":\"背景图\",\"type\":\"poster\",\"desc\":\"建议图片大小为1440*200，最多可设置1张图片。\",\"count\":1,\"default\":{\"src\":\"\\/assets\\/img\\/placeholder\\/banner_search.jpg\",\"alt\":\"背景图\",\"layout\":\"tile\",\"background\":\"#2b9cf0\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"}}}}', 
            NULL,
            'system',
            UNIX_TIMESTAMP(),
            UNIX_TIMESTAMP()
        )");
        }

    }

    private function getBlockService()
    {
        return ServiceKernel::instance()->createService('Content.BlockService');
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
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