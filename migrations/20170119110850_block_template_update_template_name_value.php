<?php

use Phpmig\Migration\Migration;

class BlockTemplateUpdateTemplateNameValue extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            UPDATE block_template SET templateName = 'block/live-top-banner.template.html.twig' WHERE code = 'live_top_banner';
            UPDATE block_template SET templateName = 'block/open-course-top-banner.template.html.twig' WHERE code = 'open_course_top_banner';
            
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            UPDATE block_template SET templateName = 'TopxiaWebBundle:Block:live_top_banner.template.html.twig' WHERE code = 'live_top_banner';
            UPDATE block_template SET templateName = 'TopxiaWebBundle:Block:open_course_top_banner.template.html.twig' WHERE code = 'open_course_top_banner'; 
        ");
    }
}
