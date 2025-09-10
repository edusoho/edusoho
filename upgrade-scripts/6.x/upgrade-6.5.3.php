<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;
use Symfony\Component\Yaml\Yaml;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
         $this->getConnection()->beginTransaction();
         try {
             $this->updateConfig();
             $this->updateScheme();
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
         ServiceKernel::instance()->createService('Crontab.CrontabService')->setNextExcutedTime(time());

    }

   
    private function updateConfig()
    {
        $filePath = $this->kernel->getParameter('kernel.root_dir').'/config/parameters.yml';
        $fileContent = file_get_contents($filePath);
        $yaml = new Yaml();
        $config = $yaml->parse($fileContent);

        if(!isset($config['parameters']['database_port']) || !is_numeric($config['parameters']['database_port'])){
            $config['parameters']['database_port'] = 3306;
            $content = $yaml->dump($config);
            $fh = fopen($filePath,"w");
            fwrite($fh,$content);
            fclose($fh);
        }
            
    }
    private function updateScheme()
    {
        $connection = $this->getConnection();
        
        if (!$this->isFieldExist('course', 'maxRate')) {
            $connection->exec("ALTER TABLE `course` ADD `maxRate` TINYINT(3) UNSIGNED NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比';");
        }

        if (!$this->isFieldExist('classroom', 'maxRate')) {
            $connection->exec("ALTER TABLE `classroom` ADD `maxRate` TINYINT(3) UNSIGNED NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比';");
        }

        if (($this->isTableExist('vip_level'))&&(!$this->isFieldExist('vip_level', 'maxRate'))) {
            $connection->exec("ALTER TABLE `vip_level` ADD `maxRate` TINYINT(3) UNSIGNED NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比';");
        }
        
    }

    public function updateBlocks()
    {
        $block = $this->getBlockService()->getBlockByCode("jianmo:bottom_info");
        if (empty($block)) {
            $this->getBlockService()->createBlock(array(
            "code" => "jianmo:bottom_info",
            "title" => "默认主题: 首页底部.链接区域 ",
            'category' => 'jianmo',
            "content" => '
            <div class="col-md-8 footer-main clearfix">
              <div class="link-item ">
              <h3>我是学生</h3>
                <ul>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/347/learn#lesson/673" target="_blank">如何注册</a>
                  </li>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/347/learn#lesson/705" target="_blank">如何学习</a>
                  </li>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/347/learn#lesson/811" target="_blank">如何互动</a>
                  </li>
                </ul>
              </div>

              <div class="link-item ">
              <h3>我是老师</h3>
                <ul>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/22" target="_blank">发布课程</a>
                  </li>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/147" target="_blank">使用题库</a>
                  </li>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/372" target="_blank">教学资料库</a>
                  </li>
                </ul>
              </div>

              <div class="link-item ">
                <h3>我是管理员</h3>
                <ul>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/340" target="_blank">系统设置</a>
                  </li>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/341" target="_blank">课程设置</a>
                  </li>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/343" target="_blank">用户管理</a>
                  </li>
                </ul>
              </div>

              <div class="link-item hidden-xs">
                <h3>商业应用</h3>
                <ul>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/232/learn#lesson/358" target="_blank">会员专区</a>
                  </li>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/232/learn#lesson/467" target="_blank">题库增强版</a>
                  </li>
                  <li>
                    <a href="http://www.qiqiuyu.com/course/380" target="_blank">用户导入导出</a>
                  </li>
                </ul>
              </div>

              <div class="link-item hidden-xs">
                <h3>关于我们</h3>
                <ul>
                    <li>
                      <a href="http://www.edusoho.com/" target="_blank">ES官网</a>
                    </li>
                    <li>
                      <a href="http://weibo.com/qiqiuyu/profile?rightmod=1&amp;wvr=6&amp;mod=personinfo" target="_blank">官方微博</a>
                    </li>
                    <li>
                      <a href="http://www.edusoho.com/abouts/joinus" target="_blank">加入我们</a>
                    </li>
                </ul>
              </div>

            </div>

            <div class="col-md-4 footer-logo hidden-sm hidden-xs">
              <a class="" href="http://www.edusoho.com" target="_blank"><img src="/assets/v2/img/bottom_logo.png?6.1.3" alt="建议图片大小为233*64"></a>
              <div class="footer-sns">
                <a href="http://weibo.com/edusoho" target="_blank"><i class="es-icon es-icon-weibo"></i></a>
                <a class="qrcode-popover top">
                  <i class="es-icon es-icon-weixin"></i>
                  <div class="qrcode-content">
                    <img src="/assets/img/default/weixin.png?6.1.3" alt="">  
                  </div>
                </a>
                <a class="qrcode-popover top">
                  <i class="es-icon es-icon-apple"></i>
                  <div class="qrcode-content">
                    <img src="/assets/img/default/apple.png?6.1.3" alt=""> 
                  </div>
                </a>
                  <a class="qrcode-popover top">
                  <i class="es-icon es-icon-android"></i>
                  <div class="qrcode-content">
                    <img src="/assets/img/default/android.png?6.1.3" alt=""> 
                  </div>
                </a>
              </div>
            </div>
            ',
            ));
        } else {
            $meta = $block['meta'];
            $data = $block['data'];
            global $kernel;
            $html = BlockToolkit::render($block, $kernel->getContainer());
            $block = $this->getBlockService()->updateBlock($block['id'], array(
                'data' => $data,
                'meta' => $meta,
                'content' => $html
            ));
        }
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

    private function getBlockService()
    {
        return ServiceKernel::instance()->createService('Content.BlockService');
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
