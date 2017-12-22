<?php

use Phpmig\Migration\Migration;

class UpdateUserNewMessageNumAndConversationUnreadNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('UPDATE user SET newMessageNum = 0;');
        $db->exec('UPDATE message_conversation SET unreadNum = 0;');
        $db->exec("UPDATE message_relation SET isRead = '1';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
