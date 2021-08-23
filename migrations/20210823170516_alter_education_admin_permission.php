<?php

use Phpmig\Migration\Migration;

class AlterEducationAdminPermission extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            UPDATE role SET data_v2 = '[\"admin_v2\",\"admin_v2_education\",\"admin_v2_education_overview\",\"admin_v2_education_overview_data\",\"admin_v2_education_overview_manage\",\"admin_v2_education_multi_class\",\"admin_v2_multi_class_inspection\",\"admin_v2_multi_class_inspection_manage\",\"admin_v2_multi_class\",\"admin_v2_multi_class_manage\",\"admin_v2_education_manage\",\"admin_v2_multi_class_product\",\"admin_v2_multi_class_product_manage\",\"admin_v2_teacher\",\"admin_v2_teacher_manage\",\"admin_v2_assistant\",\"admin_v2_assistant_manage\",\"admin_v2_multi_class_setting\",\"admin_v2_multi_class_setting_manage\",\"admin_v2_teach\",\"admin_v2_course_group\",\"admin_v2_course_show\",\"admin_v2_course_manage\",\"admin_v2_course_content_manage\",\"admin_v2_go_to_choose\",\"admin_v2_course_add\",\"admin_v2_course_set_recommend\",\"admin_v2_course_set_cancel_recommend\",\"admin_v2_course_guest_member_preview\",\"admin_v2_course_set_close\",\"admin_v2_course_sms_prepare\",\"admin_v2_course_set_clone\",\"admin_v2_course_set_publish\",\"admin_v2_course_set_delete\",\"admin_v2_course_set_remove\",\"admin_v2_course_set_recommend_list\",\"admin_v2_course_set_data\",\"admin_v2_classroom\",\"admin_v2_classroom_manage\",\"admin_v2_classroom_content_manage\",\"admin_v2_classroom_create\",\"admin_v2_classroom_cancel_recommend\",\"admin_v2_classroom_set_recommend\",\"admin_v2_classroom_close\",\"admin_v2_classroom_open\",\"admin_v2_classroom_delete\",\"admin_v2_sms_prepare\",\"admin_v2_classroom_recommend\",\"admin_v2_classroom_statistics\",\"admin_v2_live_course\",\"admin_v2_item_bank_exercise_manage\",\"admin_v2_course_category_tag\",\"admin_v2_course_category\",\"admin_v2_tag\",\"admin_v2_tool_group\",\"admin_v2_course_note\",\"admin_v2_course_question\",\"admin_v2_course_thread\",\"admin_v2_review\",\"admin_v2_cloud_resource_group\",\"admin_v2_cloud_resource\",\"admin_v2_cloud_file\",\"admin_v2_cloud_attachment\",\"admin_v2_cloud_file_setting\",\"admin_v2_cloud_attachment_setting\",\"admin_v2_question_bank\"]' WHERE code = 'ROLE_EDUCATIONAL_ADMIN';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
