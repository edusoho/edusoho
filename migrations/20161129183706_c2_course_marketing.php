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
            ALTER TABLE c2_course ADD COLUMN memberRule varchar(1024) DEFAULT '';
            ALTER TABLE c2_course ADD COLUMN joinMode varchar(32) DEFAULT 'join' COMMENT 'join,import';
            ALTER TABLE c2_course ADD COLUMN enableTryLook tinyint(1) DEFAULT 0;
            ALTER TABLE c2_course ADD COLUMN tryLookLength int(11) DEFAULT 0;
            ALTER TABLE c2_course ADD COLUMN lookLimit int(11) DEFAULT 0;
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
            ALTER TABLE c2_course DROP COLUMN memberRule;
            ALTER TABLE c2_course DROP COLUMN joinMode;
            ALTER TABLE c2_course DROP COLUMN enableTryLook;
            ALTER TABLE c2_course DROP COLUMN tryLookLength;
            ALTER TABLE c2_course DROP COLUMN lookLimit;
            ALTER TABLE c2_course DROP COLUMN services;
        ");
    }
}
