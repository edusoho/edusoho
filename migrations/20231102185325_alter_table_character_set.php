<?php

use Phpmig\Migration\Migration;

class AlterTableCharacterSet extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `biz_item` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
            ALTER TABLE `biz_question` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `biz_item` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
            ALTER TABLE `biz_question` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
        ');
    }
}
