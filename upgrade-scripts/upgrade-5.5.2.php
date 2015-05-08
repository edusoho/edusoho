<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
         $this->getConnection()->beginTransaction();
         try {
             $this->updateBlocks();
             $this->getConnection()->commit();
         } catch (\Exception $e) {
             $this->getConnection()->rollback();
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
     }

     private function updateBlocks()
     {
         global $kernel;

        //初始化系统编辑区
        BlockToolkit::init('system', realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/block.json"), $kernel->getContainer());
         $this->_updateCarouselByCode('bill_banner');
         $this->_updateCarouselByCode('live_top_banner');

        //初始化默认主题编辑区
        BlockToolkit::init('default', realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/default/block.json"), $kernel->getContainer());
         $this->_updateCarouselByCode('home_top_banner');

        //初始化清秋主题
        BlockToolkit::init('autumn', realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/autumn/block.json"), $kernel->getContainer());
         $this->_updateCarouselByCode('autumn:home_top_banner');
     }

     private function _updateCarouselByCode($code)
     {
         BlockToolkit::updateCarousel($code);
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
