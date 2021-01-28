<?php

use Phpmig\Migration\Migration;

class DeleteCloudSearchBanner extends Migration
{
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            DELETE FROM `block_template` WHERE code = 'cloud_search_banner' and category ='system';
        ");
    }

    public function down()
    {
    }
}
