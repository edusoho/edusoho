<?php

use Phpmig\Migration\Migration;

class OpenCourseAddOrg extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        if (!$this->isFieldExist('open_course', 'orgId')) {
            $db->exec(
                "ALTER TABLE `open_course` ADD COLUMN `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID';
            ");
        }

        if (!$this->isFieldExist('open_course', 'orgCode')) {
            $db->exec(
                "ALTER TABLE `open_course` ADD COLUMN `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码';
            ");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
