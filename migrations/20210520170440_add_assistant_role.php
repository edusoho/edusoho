<?php

use Phpmig\Migration\Migration;

class AddAssistantRole extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $currentTime = time();
        $biz = $this->getContainer();
        $biz['db']->exec("
            INSERT INTO `role` (`name`, `code`, `data_v2`, `createdTime`, `createdUserId`, `updatedTime`) 
            VALUES ('助教','ROLE_TEACHER_ASSISTANT','[\"admin_v2\",\"admin_v2_teach\",\"admin_v2_course_group\",\"admin_v2_multi_class\",\"admin_v2_multi_class_manage\",\"admin_v2_course_show\",\"admin_v2_course_manage\",\"admin_v2_course_content_manage\",\"web\",\"course_manage\",\"course_manage_info\",\"course_manage_base\",\"course_manage_detail\",\"course_manage_picture\",\"course_manage_lesson\",\"live_course_manage_replay\",\"course_manage_files\",\"course_manage_setting\",\"course_manage_price\",\"course_manage_teachers\",\"course_manage_students\",\"course_manage_student_create\",\"course_manage_questions\",\"course_manage_question\",\"course_manage_testpaper\",\"course_manange_operate\",\"course_manage_data\",\"course_manage_order\",\"classroom_manage\",\"classroom_manage_settings\",\"classroom_manage_set_info\",\"classroom_manage_set_price\",\"classroom_manage_set_picture\",\"classroom_manage_service\",\"classroom_manage_headteacher\",\"classroom_manage_teachers\",\"classroom_manage_assistants\",\"classroom_manage_content\",\"classroom_manage_courses\",\"classroom_manage_students\",\"classroom_manage_testpaper\"]','{$currentTime}',1,'{$currentTime}');
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            DELETE FROM `role` where `code` = 'ROLE_TEACHER_ASSISTANT';
        ");
    }
}
