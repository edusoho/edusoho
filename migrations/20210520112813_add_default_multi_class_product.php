<?php

use Phpmig\Migration\Migration;

class AddDefaultMultiClassProduct extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $currentTime = time();
        $biz = $this->getContainer();
        $biz['db']->exec("
            INSERT INTO `multi_class_product` 
                (`title`, `type`, `remark`, `createdTime`, `updatedTime`) 
            VALUES 
                ('默认产品', 'default', '系统默认产品包', '{$currentTime}', '{$currentTime}');
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            DELETE FROM `multi_class_product` where `type` = 'default';
        ");
    }
}
