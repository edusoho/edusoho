<?php

use Phpmig\Migration\Migration;

class FileGroupAddConlumn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("INSERT INTO `file_group` (`id`, `name`, `code`, `public`) VALUES (NULL, '用户导入', 'user_import', '0');");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
