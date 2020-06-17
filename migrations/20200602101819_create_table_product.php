<?php

use Phpmig\Migration\Migration;

class CreateTableProduct extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            CREATE TABLE `product` (
               `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
               `targetType` varchar (32) NOT NULL COMMENT '产品类型,course、classroom、lesson、open_course ...',
               `targetId` int(10) unsigned NOT NULL COMMENT '对应产品资源id',
               `title` varchar(1024) NOT NULL COMMENT '产品名称',
               `owner` int(10) unsigned NOT NULL COMMENT '拥有者（创建者）',
               `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
               `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
               PRIMARY KEY (`id`),
               KEY `targetType_targetId` (targetType, targetId)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('
            DROP TABLE IF EXISTS `product`;
        ');
    }
}
