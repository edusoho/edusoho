<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160926171624 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("drop table im_my_conversation");

        $this->addSql("ALTER TABLE course CHANGE conversationId convNo VARCHAR(32) NOT NULL DEFAULT ''  COMMENT '课程会话ID';");
        $this->addSql("UPDATE course SET `convNo` = '' WHERE `convNo` = '0';");

        $this->addSql("ALTER TABLE classroom CHANGE conversationId convNo VARCHAR(32) NOT NULL DEFAULT ''  COMMENT '班级会话ID';");
        $this->addSql("UPDATE classroom SET `convNo` = '' WHERE `convNo` = '0';");

        $this->addSql("
            CREATE TABLE `im_member` (
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
        $sql    = "select * from role where code='ROLE_SUPER_ADMIN';";
        $result = $this->connection->fetchAssoc($sql);
        if ($result) {
            $data = array_merge(json_decode($result['data']), array('admin_app_im'));
            $this->addSql("update role set data='".json_encode($data)."' where code='ROLE_SUPER_ADMIN';");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
