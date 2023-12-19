<?php

use Phpmig\Migration\Migration;

class ChangeArticleCharacter extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `article` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `article` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
        ');
    }
}
