<?php

use Phpmig\Migration\Migration;

class ImMember extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('drop table IF EXISTS im_my_conversation');

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `im_member`(
 	              `id` int(10) NOT NULL AUTO_INCREMENT,
 	              `convNo` varchar(32) NOT NULL COMMENT '会话ID',
 	              `targetId` int(10) NOT NULL,
 	              `targetType` varchar(15) NOT NULL,
 	              `userId` int(10) NOT NULL DEFAULT '0',
 	              `createdTime` int(10) DEFAULT '0',
 	              PRIMARY KEY (`id`)
 	            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '会话用户表';
 	        ");

        //后台IM设置权限
        $sql = "select * from role where code='ROLE_SUPER_ADMIN';";
        $result = $connection->fetchAssoc($sql);

        if ($result) {
            $data = array_merge(json_decode($result['data']), array('admin_app_im'));
            $connection->exec("update role set data='".json_encode($data)."' where code='ROLE_SUPER_ADMIN';");
        }

        $connection->exec("ALTER TABLE `im_conversation` ADD `targetType` VARCHAR(16) NOT NULL DEFAULT '' AFTER `no`");
        $connection->exec("ALTER TABLE `im_conversation` ADD `targetId` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `targetType`");
        $connection->exec("ALTER TABLE `im_conversation` ADD `title` VARCHAR(255) NOT NULL DEFAULT ''");

        $connection->exec('ALTER TABLE `im_conversation` ADD UNIQUE(`no`);');
        $connection->exec('ALTER TABLE `im_conversation` ADD INDEX targetId ( `targetId`);');
        $connection->exec('ALTER TABLE `im_conversation` ADD INDEX targetType ( `targetType`);');
        $connection->exec('ALTER TABLE `im_member` ADD INDEX convno_userId ( `convNo`, `userId` );');
        $connection->exec('ALTER TABLE `im_member` ADD INDEX userId_targetType ( `userId`,`targetType` );');

        $connection->exec("ALTER TABLE course_lesson_replay ADD `copyId` int(10) DEFAULT '0' COMMENT '复制回放的ID';");

        $connection->exec('ALTER TABLE `course_lesson` DROP `suggestHours`;');
        $connection->exec('ALTER TABLE `open_course_lesson` DROP `suggestHours`;');

        $connection->exec("ALTER TABLE `course` ADD `buyExpireTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购买开放有效期' AFTER `buyable`");

        $connection->exec("ALTER TABLE course_review add `meta` text  COMMENT '评价元信息';");
        $connection->exec("ALTER TABLE classroom_review add `meta` text  COMMENT '评价元信息'");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
