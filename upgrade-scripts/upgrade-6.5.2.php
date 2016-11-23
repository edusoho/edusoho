<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;

class EduSohoUpgrade extends AbstractUpdater
{

    public function update($index = 0)
    {
        try {
            if($index>=0 && $index<=7){
                return $this->batchDownload($index);
            }

            if($index == 8) {
                $this->vendorExtract();
                $this->replaceFiles();
                return array(
                    'index' => 9,
                    'message' => '正在解压下载后的文件',
                    'progress' => 0
                );
            }

            if($index == 9) {
                $this->replaceAutoloadFile();
                return array();
            }

        } catch (\Exception $e) {
            throw $e;
        }

        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
        return $info;
    }

    private function batchDownload($index)
    {
        $filepath = 'http://7xlcgd.dl1.z0.glb.clouddn.com/es-vendor2/vendor-'.$index.'.zip';

        $dir = ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/upgrade/vendor2';
        $filesystem = new Filesystem();
        if(!$filesystem->exists($dir)) {
            $filesystem->mkdir($dir);
        }
        $targetPath = $dir.'/vendor-'.$index.'.zip';
        touch($targetPath);
        $file = file_get_contents($filepath);
        file_put_contents($targetPath, $file);
        $index++;
        return array(
            'index' => $index,
            'message' => '下载文件'.intval($index/9*100).'%',
            'progress' => 0
        );
    }

    private function vendorExtract()
    {
        // exec('mkdir '.ServiceKernel::instance()->getParameter('kernel.root_dir').'/../vendor_v2');
        for ($i=0; $i < 8; $i++) { 
            $zip = new \ZipArchive;
            $filepath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/upgrade/vendor2/vendor-'.$i.'.zip';
            $tmpUnzipDir = ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/upgrade/vendor2';
            if ($zip->open($filepath) === TRUE) {
                $zip->extractTo($tmpUnzipDir);
                $zip->close();
                $filesystem = new Filesystem();
                $filesystem->remove($filepath);
            } else {
                throw new \Exception('无法解压缩安装包！');
            }
        }
        // $autoFilePath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/autoload.php';
        // $content = file_get_contents($autoFilePath);
        // $content = str_replace('/vendor/', '/vendor_v2/', $content);
        // file_put_contents($autoFilePath, $content);
    }

    private function replaceAutoloadFile()
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/upgrade/vendor2/edusoho/app',  ServiceKernel::instance()->getParameter('kernel.root_dir') , null, array(
            'override' => true,
            'copy_on_windows' => true
        ));
    }

    private function replaceFiles()
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/upgrade/vendor2/edusoho/vendor2',  ServiceKernel::instance()->getParameter('kernel.root_dir').'/../vendor2' , null, array(
            'override' => true,
            'copy_on_windows' => true
        ));
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    
    
    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
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
