<?php

use Phpmig\Migration\Migration;

class TestpaperItemChangeField extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();

        if (!$this->isFieldExist('testpaper_item_v8', 'type')) {
            $biz['db']->exec("
                ALTER TABLE testpaper_item_v8 ADD `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型';
            ");
        }

        if (!$this->isFieldExist('testpaper_item_result_v8', 'type')) {
            $biz['db']->exec("
                ALTER TABLE testpaper_item_result_v8 ADD `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型';
            ");
        }

        //以下仅供开发使用
        if ($this->isFieldExist('testpaper_item_v8', 'migrateType')) {
            $biz['db']->exec('
                ALTER TABLE testpaper_item_v8 drop column migrateType;
            ');

            $biz['db']->exec('
                UPDATE testpaper_item_v8 as ti, testpaper_v8 as t set ti.type = t.type where ti.testId =t.id;
            ');
        }

        if ($this->isFieldExist('testpaper_item_result_v8', 'migrateType')) {
            $biz['db']->exec('
                ALTER TABLE testpaper_item_result_v8 drop column migrateType;
            ');

            $biz['db']->exec('
                UPDATE testpaper_item_result_v8 as tr,testpaper_v8 as t set tr.type = t.type where tr.testId = t.id;
            ');
        }
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
