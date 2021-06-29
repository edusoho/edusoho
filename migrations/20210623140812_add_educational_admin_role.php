<?php

use Phpmig\Migration\Migration;

class AddEducationalAdminRole extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $currentTime = time();
        $biz = $this->getContainer();
        $biz['db']->exec("
            INSERT INTO `role` (`name`, `code`, `data_v2`, `createdTime`, `createdUserId`, `updatedTime`) VALUES ('教务', 'ROLE_EDUCATIONAL_ADMIN', '[\"admin_v2\",\"admin_v2_education\",\"admin_v2_education_manage\",\"admin_v2_multi_class_product\",\"admin_v2_multi_class_product_manage\",\"admin_v2_multi_class\",\"admin_v2_multi_class_manage\",\"admin_v2_teacher\",\"admin_v2_teacher_manage\",\"admin_v2_assistant\",\"admin_v2_assistant_manage\",\"admin_v2_teach\",\"admin_v2_course_group\",\"admin_v2_course_show\",\"admin_v2_course_manage\",\"admin_v2_course_content_manage\",\"admin_v2_go_to_choose\",\"admin_v2_course_add\",\"admin_v2_course_set_recommend\",\"admin_v2_course_set_cancel_recommend\",\"admin_v2_course_guest_member_preview\",\"admin_v2_course_set_close\",\"admin_v2_course_sms_prepare\",\"admin_v2_course_set_clone\",\"admin_v2_course_set_publish\",\"admin_v2_course_set_delete\",\"admin_v2_course_set_remove\",\"admin_v2_course_set_recommend_list\",\"admin_v2_course_set_data\",\"admin_v2_classroom\",\"admin_v2_classroom_manage\",\"admin_v2_classroom_content_manage\",\"admin_v2_classroom_create\",\"admin_v2_classroom_cancel_recommend\",\"admin_v2_classroom_set_recommend\",\"admin_v2_classroom_close\",\"admin_v2_classroom_open\",\"admin_v2_classroom_delete\",\"admin_v2_sms_prepare\",\"admin_v2_classroom_recommend\",\"admin_v2_classroom_statistics\",\"admin_v2_live_course\",\"admin_v2_item_bank_exercise_manage\",\"admin_v2_course_category_tag\",\"admin_v2_course_category\",\"admin_v2_tag\",\"admin_v2_tool_group\",\"admin_v2_course_note\",\"admin_v2_course_question\",\"admin_v2_course_thread\",\"admin_v2_review\",\"admin_v2_cloud_resource_group\",\"admin_v2_cloud_resource\",\"admin_v2_cloud_file\",\"admin_v2_cloud_attachment\",\"admin_v2_cloud_file_setting\",\"admin_v2_cloud_attachment_setting\",\"admin_v2_question_bank\",\"web\",\"course_manage\",\"course_manage_info\",\"course_manage_base\",\"course_manage_detail\",\"course_manage_picture\",\"course_manage_lesson\",\"live_course_manage_replay\",\"course_manage_files\",\"course_manage_setting\",\"course_manage_price\",\"course_manage_teachers\",\"course_manage_students\",\"course_manage_student_create\",\"course_manage_questions\",\"course_manage_question\",\"course_manage_testpaper\",\"course_manange_operate\",\"course_manage_data\",\"course_manage_order\",\"classroom_manage\",\"classroom_manage_settings\",\"classroom_manage_set_info\",\"classroom_manage_set_price\",\"classroom_manage_set_picture\",\"classroom_manage_service\",\"classroom_manage_headteacher\",\"classroom_manage_teachers\",\"classroom_manage_assistants\",\"classroom_manage_content\",\"classroom_manage_courses\",\"classroom_manage_students\",\"classroom_manage_testpaper\"]', '{$currentTime}', 1, '{$currentTime}');
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            DELETE FROM `role` where `code` = 'ROLE_EDUCATIONAL_ADMIN';
        ");
    }
}
