<?php

use Phpmig\Migration\Migration;

class DeleteUnneedIndexes extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('
            DROP INDEX `announcement_targetType_startTime_endTime_index` ON `announcement`;
            DROP INDEX `classroom_courses_courseId_index` ON `classroom_courses`;
            DROP INDEX `course_favorite_userId_courseSetId_type_index` ON `course_favorite`;
            DROP INDEX `index_role_userId` ON `course_member`;
            DROP INDEX `coursesetid_status` ON `course_note`;
        ');

        $connection->exec('
            DROP INDEX `course_task_courseId_status_index` ON `course_task`;
            DROP INDEX `course_task_result_courseId_userId_index` ON `course_task_result`;
            DROP INDEX `course_task_result_courseTaskId_userId_index` ON `course_task_result`;
            DROP INDEX `taskid_userid` ON `course_task_result`;
            DROP INDEX `uri` ON `file`;
        ');

        $connection->exec('
            DROP INDEX `navigation_type_isOpen_orgId_index` ON `navigation`;
            DROP INDEX `userid_type` ON `navigation`;
        ');

        $connection->exec('
            DROP INDEX `userid_type_object` ON `status`;
            DROP INDEX `tag_owner_ownerType_ownerId_index` ON `tag_owner`;
        ');

        $connection->exec('
            DROP INDEX `theme_config_name_uindex` ON `theme_config`;
            DROP INDEX `convertHash` ON `upload_files`;
            DROP INDEX `promoted` ON `user`;
        ');

        $connection->exec('
            DROP INDEX `userId_createdTime` ON `user_active_log`;
        ');
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $sql = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
