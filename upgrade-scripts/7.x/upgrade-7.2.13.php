<?php

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Topxia\Service\Common\ServiceKernel;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            if ($index >= 0 && $index <= 17) {
                return $this->batchDownload($index);
            }

            if ($index == 18) {
                $this->vendorExtract();
                $this->replaceFiles();
                return array(
                    'index'    => 19,
                    'message'  => '正在解压下载后的文件',
                    'progress' => 0
                );
            }

            if ($index == 19) {
                $this->checkBizInVendor();
                return array(
                    'index'    => 20,
                    'message'  => '正在执行升级脚本',
                    'progress' => 0
                );
            }

            if($index == 20){
                $this->updateScheme();
                $this->getConnection()->commit();
            }

        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir') . "../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('Crontab.CrontabService')->setNextExcutedTime(time());

    }

    private function updateScheme()
    {
        $connection = $this->getConnection();

        if (!$this->isFieldExist('user', 'registeredWay')) {
            $connection->exec("ALTER TABLE `user` ADD `registeredWay` varchar(64) NOT NULL DEFAULT '' COMMENT '注册设备来源(web/ios/android)'");
        }
    }

    private function checkBizInVendor()
    {
        $filesystem  = new Filesystem();
        $codeagesDir = ServiceKernel::instance()->getParameter('kernel.root_dir') . '/../vendor/codeages';
        if (!$filesystem->exists($codeagesDir)) {
            throw new \Exception('vendor包缺少必要组件');
        } else {
            return array();
        }
    }

    private function batchDownload($index)
    {
        $filepath = 'http://cdn.qiqiuyun.net/es-vendor/v1/vendor' . ($index + 1) . '.zip';

        $dir        = ServiceKernel::instance()->getParameter('kernel.root_dir') . '/data/upgrade/course-8.0';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($dir)) {
            $filesystem->mkdir($dir);
        }
        $targetPath = $dir . '/vendor' . $index . '.zip';
        touch($targetPath);
        $file = file_get_contents($filepath);
        file_put_contents($targetPath, $file);
        $index++;
        return array(
            'index'    => $index,
            'message'  => '下载文件中'. intval($index / 20 * 100) . '%, 还剩余' . intval(18 - $index) . 'M',
            'progress' => 0
        );
    }

    private function vendorExtract()
    {
        $filesystem = new Filesystem();
        foreach (range(0, 17) as $index) {
            $zip         = new \ZipArchive;
            $filepath    = ServiceKernel::instance()->getParameter('kernel.root_dir') . '/data/upgrade/course-8.0/vendor' . $index . '.zip';
            $tmpUnzipDir = ServiceKernel::instance()->getParameter('kernel.root_dir') . '/data/upgrade/course-8.0';

            if ($zip->open($filepath) === true) {
                $zip->extractTo($tmpUnzipDir);
                $zip->close();
                $filesystem->remove($filepath);
            } else {
                throw new \Exception('无法解压缩安装包！');
            }
        }
    }

    private function replaceFiles()
    {
        $filesystem = new Filesystem();
        $vendorDir  = ServiceKernel::instance()->getParameter('kernel.root_dir') . '/../vendor';

        if ($filesystem->exists($vendorDir)) {
            try {
                $filesystem->remove($vendorDir);
            } catch (IOException $e) {
                throw new \Exception("无法清空vendor目录");
            }
        }

        $filesystem->mirror(ServiceKernel::instance()->getParameter('kernel.root_dir') . '/data/upgrade/course-8.0/vendor', ServiceKernel::instance()->getParameter('kernel.root_dir') . '/../vendor', null, array(
            'override'        => true,
            'copy_on_windows' => true
        ));
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql    = "show index from `{$table}`  where Key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }


}

abstract class AbstractUpdater
{
    protected $kernel;

    public function __construct($kernel)
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
