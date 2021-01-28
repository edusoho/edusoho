<?php

use Phpmig\Migration\Migration;

class InitSystemUsers extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();

        $result = $this->getUserByType();
        if (!empty($result)) {
            return;
        }

        $biz['db']->exec("
            INSERT INTO `user` (`email`, `verifiedMobile`, `password`, `salt`, `payPassword`, `payPasswordSalt`, `locale`, `uri`, `nickname`, `title`, `tags`, `type`, `point`, `coin`, `smallAvatar`, `mediumAvatar`, `largeAvatar`, `emailVerified`, `setup`, `roles`, `promoted`, `promotedSeq`, `promotedTime`, `locked`, `lockDeadline`, `consecutivePasswordErrorTimes`, `lastPasswordFailTime`, `loginTime`, `loginIp`, `loginSessionId`, `approvalTime`, `approvalStatus`, `newMessageNum`, `newNotificationNum`, `createdIp`, `createdTime`, `updatedTime`, `inviteCode`, `orgId`, `orgCode`, `registeredWay`) VALUES
('user_tfo2ex19h@edusoho.net', '', '3DMYb8GyEXk32ruFzw4lxy2elz6/aoPtA5X8vCTWezg=', 'qunt972ow5c48k4wc8k0ss448os0oko', '', '', NULL, '', 'user70rbkm(系统用户)', '', '', 'system', 0, 0, '', '', '', 1, 1, '|ROLE_USER|ROLE_SUPER_ADMIN|', 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 'unapprove', 0, 0, '', 1489204100, 1489204100, NULL, 1, '1.', '');
        ");

        $result = $this->getUserByType();

        $sql = "INSERT INTO `user_profile` (`id`, `truename`, `idcard`, `gender`, `iam`, `birthday`, `city`, `mobile`, `qq`, `signature`, `about`, `company`, `job`, `school`, `class`, `weibo`, `weixin`, `isQQPublic`, `isWeixinPublic`, `isWeiboPublic`, `site`, `intField1`, `intField2`, `intField3`, `intField4`, `intField5`, `dateField1`, `dateField2`, `dateField3`, `dateField4`, `dateField5`, `floatField1`, `floatField2`, `floatField3`, `floatField4`, `floatField5`, `varcharField1`, `varcharField2`, `varcharField3`, `varcharField4`, `varcharField5`, `varcharField6`, `varcharField7`, `varcharField8`, `varcharField9`, `varcharField10`, `textField1`, `textField2`, `textField3`, `textField4`, `textField5`, `textField6`, `textField7`, `textField8`, `textField9`, `textField10`) VALUES
({$result['id']}, '', '', 'secret', '', NULL, '', '', '', NULL, NULL, '', '', '', '', '', '', 0, 0, 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";

        $biz['db']->exec($sql);
    }

    private function getUserByType()
    {
        $biz = $this->getContainer();
        $sql = "select * from user where type='system' limit 1;";
        $result = $biz['db']->fetchAssoc($sql);

        return $result;
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
