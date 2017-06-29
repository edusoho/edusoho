<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160225171530 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `announcement` CHANGE `targetId` `targetId` INT(10) UNSIGNED NOT NULL COMMENT '所属ID';");
        $this->addSql("ALTER TABLE `block` CHANGE `code` `code` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '编辑区编码';");
        $this->addSql("ALTER TABLE `cash_orders` CHANGE `payment` `payment` ENUM('none','alipay','wxpay','heepay','quickpay','iosiap') NOT NULL DEFAULT 'none';");
        $this->addSql("ALTER TABLE `coupon` CHANGE `targetId` `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用对象';");

        $this->addSql("ALTER TABLE `coupon` CHANGE `receiveTime` `receiveTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收时间';");

        $this->addSql("ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin','wxpay','heepay','quickpay','iosiap') NOT NULL DEFAULT 'none' COMMENT '订单支付方式';");

        $this->addSql("update testpaper_result set passedStatus='none' where passedStatus is null;");

        $this->addSql("ALTER TABLE `testpaper_result` CHANGE `passedStatus` `passedStatus` enum('none','excellent','good','passed','unpassed') NOT NULL DEFAULT 'none' COMMENT '考试通过状态，none表示该考试没有';");

        $this->addSql("ALTER TABLE `thread_member` CHANGE `createdTIme` `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间';");
        $this->addSql("ALTER TABLE `upload_files` CHANGE `type` `type` enum('document','video','audio','image','ppt','other','flash') NOT NULL DEFAULT 'other' COMMENT '文件类型';");

        if (!$this->isFieldExist('testpaper_item_result', 'pId')) {
            $this->addSql("ALTER TABLE `testpaper_item_result` ADD `pId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '复制试卷题目Id';");
        }

        if ($this->isFieldExist('user_pay_agreement', 'otherId')) {
            $this->addSql("ALTER TABLE `user_pay_agreement` CHANGE `otherId` `bankId` int(8) NOT NULL COMMENT '对应的银行Id';");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}
