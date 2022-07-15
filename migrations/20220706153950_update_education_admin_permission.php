<?php

use Phpmig\Migration\Migration;

class UpdateEducationAdminPermission extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            UPDATE role SET data_v2 = '[\"admin_v2\",\"admin_v2_education\",\"admin_v2_education_overview\",\"admin_v2_education_overview_data\",\"admin_v2_education_overview_manage\",\"admin_v2_education_multi_class\",\"admin_v2_multi_class_inspection\",\"admin_v2_multi_class_inspection_manage\",\"admin_v2_multi_class\",\"admin_v2_multi_class_manage\",\"admin_v2_education_manage\",\"admin_v2_multi_class_product\",\"admin_v2_multi_class_product_manage\",\"admin_v2_teacher\",\"admin_v2_teacher_manage\",\"admin_v2_assistant\",\"admin_v2_assistant_manage\",\"admin_v2_multi_class_setting\",\"admin_v2_multi_class_setting_manage\"]' WHERE code = 'ROLE_EDUCATIONAL_ADMIN';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
