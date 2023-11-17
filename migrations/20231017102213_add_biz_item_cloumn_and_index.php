<?php

use Phpmig\Migration\Migration;

class AddBizItemCloumnAndIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $connection = $this->getContainer()['db'];
        if (!$this->isFieldExist('biz_item', 'material_hash')) {
            $connection->exec("ALTER TABLE `biz_item` ADD COLUMN `material_hash` char(32) NOT NULL DEFAULT '' COMMENT '题目材料hash' AFTER `material`;");
        }

        if (!$this->isIndexExist('biz_item', 'bank_id_material_hash')) {
            $connection->exec('ALTER TABLE `biz_item` ADD INDEX `bank_id_material_hash` (`bank_id`, `material_hash`);');
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $connection = $this->getContainer()['db'];
        if ($this->isFieldExist('biz_item', 'material_hash')) {
            $connection->exec('ALTER TABLE `biz_item` DROP COLUMN `material_hash`');
        }

        if ($this->isIndexExist('biz_item', 'bank_id_material_hash')) {
            $connection->exec('ALTER TABLE `biz_item` DROP INDEX `bank_id_material_hash`;');
        }
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}`  where Key_name='{$indexName}';";
        $result = $this->getContainer()['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
