<?php

use Phpmig\Migration\Migration;

class QuestionFavorite extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            ALTER TABLE question_favorite add targetType VARCHAR(50) NOT NULL DEFAULT '' AFTER `questionId`
        ");

        $db->exec("
            ALTER TABLE question_favorite add targetId INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `targetType`
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE `question_favorite` DROP `targetType`');
        $db->exec('ALTER TABLE `question_favorite` DROP `targetId`');
    }
}
