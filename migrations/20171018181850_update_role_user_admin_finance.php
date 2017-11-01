<?php

use Phpmig\Migration\Migration;

class UpdateRoleUserAdminFinance extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $roles = array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN');

        foreach ($roles as $role) {
            $sql = "select * from role where code='{$role}';";
            $result = $connection->fetchAssoc($sql);
            if ($result) {
                $data = array_merge(json_decode($result['data']), array('admin_orders', 'admin_order_manage', 'admin_order_refunds', 'admin_order_refunds_manage'));
                $connection->exec("update role set data='".json_encode($data)."' where code='{$role}';");
            }
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
