<?php

use Phpmig\Migration\Migration;

class UpdateCourseMemberCount extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
          UPDATE `course_member` SET `learnedRequiredNum` = `learnedNum`;
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
