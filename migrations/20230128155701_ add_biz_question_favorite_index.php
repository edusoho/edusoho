<?php

use Phpmig\Migration\Migration;

class AddBizQuestionFavoriteIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $connection = $this->getContainer()['db'];
        if (!$this->isIndexExist('biz_question_favorite', 'user_id_and_target_type')) {
            $connection->exec("ALTER TABLE `biz_question_favorite` ADD INDEX `user_id_and_target_type` (`user_id`, `target_type`);");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $connection = $this->getContainer()['db'];
        if ($this->isIndexExist('biz_question_favorite', 'user_id_and_target_type')) {
            $connection->exec('ALTER TABLE `biz_question_favorite` DROP INDEX `user_id_and_target_type`;');
        }

    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}`  where Key_name='{$indexName}';";
        $result = $this->getContainer()['db']->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}
