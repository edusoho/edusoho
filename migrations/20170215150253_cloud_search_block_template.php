<?php

use Phpmig\Migration\Migration;

class CloudSearchBlockTemplate extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            INSERT INTO `block_template` (`title`, `mode`, `template`, `templateName`, `templateData`, `content`, `data`, `code`, `meta`, `tips`, `category`, `createdTime`, `updateTime`) 
            VALUES(
                '云搜索背景图',
                'template',
                NULL, 
                'TopxiaWebBundle:Block:cloud_search_banner.template.html.twig',
                NULL,
                '', 
                '{\"posters\":[{\"src\":\"\\/assets\\/img\\/placeholder\\/banner_search.jpg\",\"alt\":\"背景图\",\"layout\":\"tile\",\"background\":\"#2b9cf0\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"}]}',
                'cloud_search_banner', 
                '{\"title\":\"背景图\",\"category\":\"system\",\"templateName\":\"TopxiaWebBundle:Block:cloud_search_banner.template.html.twig\",\"items\":{\"posters\":{\"title\":\"背景图\",\"type\":\"poster\",\"desc\":\"建议图片大小为1440*200，最多可设置1张图片。\",\"count\":1,\"default\":{\"src\":\"\\/assets\\/img\\/placeholder\\/banner_search.jpg\",\"alt\":\"背景图\",\"layout\":\"tile\",\"background\":\"#2b9cf0\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"}}}}', 
                NULL, 'system', 1486538595, 1486543476);
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
