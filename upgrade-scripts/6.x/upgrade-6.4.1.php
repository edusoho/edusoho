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
             $this->updateScheme();
             $this->updateBlock();
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
         ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    protected function updateBlock()
    {
        $block = $this->getBlockService()->getBlockByCode('jianmo:home_top_banner');
        if(!empty($block) && isset($block['data']['carousel1ground'])){
            $this->replaceData($block);

            $this->replaceMetaItems($block);

            $this->replaceContent($block);

            $block = $this->getBlockService()->updateBlock($block['id'],$block);
        }
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();
        $sql = "SELECT count(*) FROM (SELECT fromId,count(*) AS fcount FROM `user_bind` WHERE type IN ('weixin','weixinmob','weixinweb') GROUP BY fromId) AS f WHERE fcount>1;";
        $count = $this->getConnection()->fetchColumn($sql) ? : 0;
        for ($i=0; $i < $count; $i++) { 
            $connection->exec("DELETE FROM `user_bind` WHERE fromId IN (SELECT f.fromId FROM (SELECT fromId,count(*) AS fcount FROM `user_bind` WHERE type IN ('weixin','weixinmob','weixinweb') GROUP BY fromId) AS f WHERE fcount>1) ORDER BY createdTime DESC LIMIT 1;");
        }

        for ($i=0; $i < $count; $i++) { 
            $connection->exec("DELETE FROM `user_bind` WHERE fromId IN (SELECT f.fromId FROM (SELECT fromId,count(*) AS fcount FROM `user_bind` WHERE type IN ('weixin','weixinmob','weixinweb') GROUP BY fromId) AS f WHERE fcount>1) ORDER BY createdTime DESC LIMIT 1;");
        }
        $connection->exec("UPDATE `user_bind` SET `type`='weixin' WHERE `type` in ('weixinmob','weixinweb');");
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
    
    protected function replaceData(&$block)
    {
        $oldData = $block['data'];
        $newHtml = $this->oldDataConvertHtml($oldData);
        $newData = array(
            'posters' => array(
                array(
                    'src' => $oldData['carousel1ground'][0]['src'],
                    'alt' => "海报1",
                    'layout' => 'limitWide',
                    'background' => $oldData['carousel1background'][0]['value'],
                    'href' => $oldData['carousel1ground'][0]['href'],
                    'html' => $newHtml[0],
                    'status' => 1,
                    'mode' => 'html'
                ), array(
                    'src' => $oldData['carousel2ground'][0]['src'],
                    'alt' => "海报2",
                    'layout' => 'limitWide',
                    'background' => $oldData['carousel2background'][0]['value'],
                    'href' => $oldData['carousel2ground'][0]['href'],
                    'html' => $newHtml[1],
                    'status' => 1,
                    'mode' => 'html'
                ), array(
                    'src' => $oldData['carousel3ground'][0]['src'],
                    'alt' => "海报3",
                    'layout' => 'limitWide',
                    'background' => $oldData['carousel3background'][0]['value'],
                    'href' => $oldData['carousel3ground'][0]['href'],
                    'html' => $newHtml[2],
                    'status' => 1,
                    'mode' => 'html'
                ), array(
                    'src' => '/themes/jianmo/img/banner_net.jpg',
                    'alt' => "海报4",
                    'layout' => 'tile',
                    'background' => "#3ec768",
                    'href' => "",
                    'html' => "",
                    'status' => 0,
                    'mode' => 'img'
                ), array(
                    'src' => '/themes/jianmo/img/banner_net.jpg',
                    'alt' => "海报5",
                    'layout' => 'tile',
                    'background' => "#3ec768",
                    'href' => "",
                    'html' => "",
                    'status' => 0,
                    'mode' => 'img'
                ), array(
                    'src' => '/themes/jianmo/img/banner_net.jpg',
                    'alt' => "海报6",
                    'layout' => 'tile',
                    'background' => "#3ec768",
                    'href' => "",
                    'html' => "",
                    'status' => 0,
                    'mode' => 'img'
                ), array(
                    'src' => '/themes/jianmo/img/banner_net.jpg',
                    'alt' => "海报7",
                    'layout' => 'tile',
                    'background' => "#3ec768",
                    'href' => "",
                    'html' => "",
                    'status' => 0,
                    'mode' => 'img'
                ), array(
                    'src' => '/themes/jianmo/img/banner_net.jpg',
                    'alt' => "海报8",
                    'layout' => 'tile',
                    'background' => "#3ec768",
                    'href' => "",
                    'html' => "",
                    'status' => 0,
                    'mode' => 'img'
                ),
            )
        );

        $block['data'] = $newData;
    }

    protected function replaceMetaItems(&$block)
    {
        $default = array();
        foreach ( $block['data']['posters'] as $poster){
            $default[] = $poster;
        }
        $newItems = array(
            "title" => "海报",
            "desc" => "首页海报",
            "count" => 1,
            "type" => "poster",
            "default" => $default
        );

        $block['meta']['items'] = array(
            "posters" => $newItems
        );
    }

    protected function replaceContent(&$block)
    {
        global $kernel;
        $newContent = BlockToolkit::render($block, $kernel->getContainer());
        $block['content'] = $newContent;
    }

    protected function oldDataConvertHtml($oldData)
    {
        $html1 = !empty($oldData['carousel1banner']) ?
            "<div class=\"swiper-slide swiper-hidden\" style=\"background: {$oldData['carousel1background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel1ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel1ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel1title1'][0]['value']}</span><span>{$oldData['carousel1title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel1subtitle1'][0]['value']}</span><span>{$oldData['carousel1subtitle2'][0]['value']}</span>
                            </div>
                            <div class=\"item-mac\">
                                <img class=\"img-responsive\" src=\"{$oldData['carousel1banner'][0]['src']}\">
                            </div>
                        </div>
                    </a>
                </div>
            </div>"
            :
            "<div class=\"swiper-slide swiper-hidden\" style=\"background: {$oldData['carousel1background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel1ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel1ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel1title1'][0]['value']}</span><span>{$oldData['carousel1title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel1subtitle1'][0]['value']}</span><span>{$oldData['carousel1subtitle2'][0]['value']}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>";

        $html2 = !empty($oldData['carousel2banner']) ?
            "<div class=\"swiper-slide swiper-hidden\" style=\"background: {$oldData['carousel2background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel2ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel2ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel2title1'][0]['value']}</span><span>{$oldData['carousel2title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel2subtitle1'][0]['value']}</span><span>{$oldData['carousel2subtitle2'][0]['value']}</span>
                            </div>
                            <div class=\"item-mac\">
                                <img class=\"img-responsive\" src=\"{$oldData['carousel2banner'][0]['src']}\">
                            </div>
                        </div>
                    </a>
                </div>
            </div>"
            :
            "<div class=\"swiper-slide swiper-hidden\" style=\"background: {$oldData['carousel2background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel2ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel2ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel2title1'][0]['value']}</span><span>{$oldData['carousel2title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel2subtitle1'][0]['value']}</span><span>{$oldData['carousel2subtitle2'][0]['value']}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>";

        $html3 = !empty($oldData['carousel3banner']) ?
            "<div class=\"swiper-slide swiper-hidden\" style=\"background: {$oldData['carousel3background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel3ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel3ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel3title1'][0]['value']}</span><span>{$oldData['carousel3title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel3subtitle1'][0]['value']}</span><span>{$oldData['carousel3subtitle2'][0]['value']}</span>
                            </div>
                            <div class=\"item-mac\">
                                <img class=\"img-responsive\" src=\"{$oldData['carousel3banner'][0]['src']}\">
                            </div>
                        </div>
                    </a>
                </div>
            </div>"
            :
            "<div class=\"swiper-slide swiper-hidden\" style=\"background: {$oldData['carousel3background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel3ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel3ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel3title1'][0]['value']}</span><span>{$oldData['carousel3title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel3subtitle1'][0]['value']}</span><span>{$oldData['carousel3subtitle2'][0]['value']}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>";


        return array($html1, $html2, $html3);
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
