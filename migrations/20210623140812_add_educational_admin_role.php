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
            INSERT INTO `role` (`name`, `code`, `data_v2`, `createdTime`, `createdUserId`, `updatedTime`) VALUES ('教务', 'ROLE_EDUCATIONAL_ADMIN', '[\"admin_v2\",\"admin_v2_education\",\"admin_v2_education_manage\",\"admin_v2_multi_class_product\",\"admin_v2_multi_class_product_manage\",\"admin_v2_multi_class\",\"admin_v2_multi_class_manage\",\"admin_v2_teacher\",\"admin_v2_teacher_manage\",\"admin_v2_assistant\",\"admin_v2_assistant_manage\"]', '{$currentTime}', 1, '{$currentTime}');
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
