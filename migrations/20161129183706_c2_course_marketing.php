<?php

use Phpmig\Migration\Migration;

class C2CourseMarketing extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course ADD COLUMN isFree tinyint(1) DEFAULT 0;
            ALTER TABLE c2_course ADD COLUMN price int(11) DEFAULT 0;
            ALTER TABLE c2_course ADD COLUMN vipLevelId int(11) DEFAULT 0;
            ALTER TABLE c2_course ADD COLUMN buyable tinyint(1) DEFAULT 1;
            ALTER TABLE c2_course ADD COLUMN tryLookable tinyint(1) DEFAULT 0;
            ALTER TABLE c2_course ADD COLUMN tryLookLength int(11) DEFAULT 0;
            ALTER TABLE c2_course ADD COLUMN watchLimit int(11) DEFAULT 0;
            ALTER TABLE c2_course ADD COLUMN services text;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE c2_course DROP COLUMN isFree;
            ALTER TABLE c2_course DROP COLUMN price;
            ALTER TABLE c2_course DROP COLUMN vipLevelId;
            ALTER TABLE c2_course DROP COLUMN buyable;
            ALTER TABLE c2_course DROP COLUMN tryLookable;
            ALTER TABLE c2_course DROP COLUMN tryLookLength;
            ALTER TABLE c2_course DROP COLUMN watchLimit;
            ALTER TABLE c2_course DROP COLUMN services;
        ");
    }
}
