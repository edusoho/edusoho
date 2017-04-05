<?php

class OtherMigrate extends AbstractMigrate
{
    private function migrate1()
    {
        if ($this->isFieldExist('course_note', 'lessonId')) {
            $this->exec(
                "ALTER TABLE `course_note` CHANGE `lessonId` `taskId` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID';"
            );
        }

        if (!$this->isFieldExist('course_note', 'courseSetId')) {
            $this->exec("ALTER TABLE `course_note` ADD COLUMN `courseSetId` INT(10) UNSIGNED NOT NULL;");
        }

        $this->exec("UPDATE course_note SET courseSetId = courseId");
    }

    private function migrate2()
    {
        if (!$this->isFieldExist('course_review', 'courseSetId')) {
            $this->exec("ALTER TABLE `course_review` add COLUMN `courseSetId` int(10) UNSIGNED NOT NULL DEFAULT '0';");
        }
        $this->exec("UPDATE course_review SET courseSetId = courseId");
    }

    private function migrate3()
    {
        if (!$this->isFieldExist('course_thread', 'courseSetId')) {
            $this->exec("ALTER TABLE `course_thread` ADD courseSetId INT(10) UNSIGNED NOT NULL;");
        }
        $this->exec("UPDATE course_thread SET courseSetId = courseId");

        if ($this->isFieldExist('course_thread', 'lessonId')) {
            $this->exec(
                "ALTER TABLE `course_thread` CHANGE `lessonId` `taskId` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID';"
            );
        }
    }

    private function migrate4()
    {
        if ($this->isFieldExist('course_thread_post', 'lessonId')) {
            $this->exec(
                "ALTER TABLE `course_thread_post` CHANGE `lessonId` `taskId` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID';"
            );
        }
    }

    private function migrate5()
    {
        if (!$this->isFieldExist('course_favorite', 'courseSetId')) {
            $this->exec('ALTER TABLE course_favorite ADD courseSetId INT(10) NOT NULL DEFAULT 0 COMMENT "课程ID";');
        }
        $this->exec("UPDATE course_favorite SET courseSetId = courseId");

        if ($this->isFieldExist('course_favorite', 'courseId')) {
            $this->exec("ALTER TABLE course_favorite MODIFY courseId INT(10) unsigned NOT NULL COMMENT '教学计划ID';");
        }
    }

    private function migrate6()
    {
        if (!$this->isFieldExist('course_material', 'courseSetId')) {
            $this->exec("ALTER TABLE course_material ADD COLUMN courseSetId int(10) default 0 COMMENT '课程ID';");
        }
        $this->exec("UPDATE course_material SET courseSetId = courseId");
    }

    private function migrate7()
    {

        if (!$this->isFieldExist('course_member', 'courseSetId')) {
            $this->exec(
                "ALTER TABLE `course_member` ADD COLUMN  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID';"
            );
        }
        $this->exec("UPDATE course_member SET courseSetId = courseId");
    }

    private function migrate14()
    {
        if ($this->isFieldExist('course_member', 'courseId')) {
            $this->exec("ALTER TABLE `course_member` MODIFY courseId INT(10) unsigned NOT NULL COMMENT '教学计划ID';");
        }
        $this->exec(
            'UPDATE course_member AS cm INNER JOIN course_v8 c ON c.id = cm.courseId SET cm.courseSetId=c.courseSetId;'
        );
    }

    private function migrate8()
    {
        if (!$this->isFieldExist('classroom_courses', 'courseSetId')) {
            $this->exec(
                "ALTER TABLE `classroom_courses` ADD COLUMN `courseSetId` INT(10) NOT NULL DEFAULT '0' COMMENT '课程ID';"
            );
        }
        $this->exec("UPDATE classroom_courses SET courseSetId = courseId");
    }

    private function migrate9()
    {
        $this->exec(
            "UPDATE block_template SET templateName = 'block/live-top-banner.template.html.twig' WHERE code = 'live_top_banner';"
        );
        $this->exec(
            "UPDATE block_template SET templateName = 'block/open-course-top-banner.template.html.twig' WHERE code = 'open_course_top_banner';"
        );

        $this->exec(
            "UPDATE `block_template` SET templateName = 'block/cloud-search-banner.template.html.twig' WHERE code = 'cloud_search_banner';"
        );
    }

    private function migrate10()
    {
        $this->exec(
            "UPDATE crontab_job SET targetType = 'task' WHERE targetType = 'lesson' AND name = 'SmsSendOneDayJob';"
        );
        $this->exec(
            "UPDATE crontab_job SET targetType = 'task' WHERE targetType = 'lesson' AND name = 'SmsSendOneHourJob';"
        );
    }

    private function migrate11()
    {
        $result = $this->getUserByType();

        if (empty($result)) {
            $this->exec("
                INSERT INTO `user` (`email`, `verifiedMobile`, `password`, `salt`, `payPassword`, `payPasswordSalt`, `locale`, `uri`, `nickname`, `title`, `tags`, `type`, `point`, `coin`, `smallAvatar`, `mediumAvatar`, `largeAvatar`, `emailVerified`, `setup`, `roles`, `promoted`, `promotedSeq`, `promotedTime`, `locked`, `lockDeadline`, `consecutivePasswordErrorTimes`, `lastPasswordFailTime`, `loginTime`, `loginIp`, `loginSessionId`, `approvalTime`, `approvalStatus`, `newMessageNum`, `newNotificationNum`, `createdIp`, `createdTime`, `updatedTime`, `inviteCode`, `orgId`, `orgCode`, `registeredWay`) VALUES
    ('user_tfo2ex19h@edusoho.net', '', '3DMYb8GyEXk32ruFzw4lxy2elz6/aoPtA5X8vCTWezg=', 'qunt972ow5c48k4wc8k0ss448os0oko', '', '', NULL, '', 'user70rbkm(系统用户)', '', '', 'system', 0, 0, '', '', '', 1, 1, '|ROLE_USER|ROLE_SUPER_ADMIN|', 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 'unapprove', 0, 0, '', 1489204100, 1489204100, NULL, 1, '1.', '');
            ");

            $result = $this->getUserByType();

            $sql = "INSERT INTO `user_profile` (`id`, `truename`, `idcard`, `gender`, `iam`, `birthday`, `city`, `mobile`, `qq`, `signature`, `about`, `company`, `job`, `school`, `class`, `weibo`, `weixin`, `isQQPublic`, `isWeixinPublic`, `isWeiboPublic`, `site`, `intField1`, `intField2`, `intField3`, `intField4`, `intField5`, `dateField1`, `dateField2`, `dateField3`, `dateField4`, `dateField5`, `floatField1`, `floatField2`, `floatField3`, `floatField4`, `floatField5`, `varcharField1`, `varcharField2`, `varcharField3`, `varcharField4`, `varcharField5`, `varcharField6`, `varcharField7`, `varcharField8`, `varcharField9`, `varcharField10`, `textField1`, `textField2`, `textField3`, `textField4`, `textField5`, `textField6`, `textField7`, `textField8`, `textField9`, `textField10`) VALUES
    ('{$result['id']}', '', '', 'secret', '', NULL, '', '', '', NULL, NULL, '', '', '', '', '', '', 0, 0, 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";

            $this->exec($sql);
        }
    }

    private function migrate12()
    {
        if ($this->isFieldExist('course_draft', 'lessonId')) {
            $this->exec("ALTER TABLE course_draft CHANGE lessonId activityId INT(10) unsigned NOT NULL COMMENT '教学活动ID';");
        }
    }

    private function migrate13()
    {
        if (!$this->isFieldExist('classroom', 'creator')) {
            $this->exec("ALTER TABLE classroom ADD `creator` int(10) NOT NULL DEFAULT '0' COMMENT '班级创建者';");
            $this->exec("UPDATE `classroom` SET `creator` = `headTeacherId` WHERE `creator` = 0;");
        }
    }

    private function getUserByType()
    {
        $sql = "select * from user where type='system' limit 1;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return $result;
    }

    public function update($page)
    {
        if ($page>14) {
            return;
        }

        $method = "migrate{$page}";
        $this->$method();
        return $page+1;
    }
}
