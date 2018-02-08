<?php

use Phpmig\Migration\Migration;

class BlockTemplateReupdateCloudSearchBanner extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("UPDATE `block_template` SET templateName = 'block/cloud-search-banner.template.html.twig' WHERE code = 'cloud_search_banner'");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
