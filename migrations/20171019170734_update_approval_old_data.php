<?php

use Phpmig\Migration\Migration;

class UpdateApprovalOldData extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("UPDATE user_approval as ua, user as u SET ua.status = 'approved' WHERE ua.userId = u.id AND u.approvalStatus = 'approved' AND ua.status = 'approving'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
