<?php

class CourseSetMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('course_set_v8')) {
            $sql = "CREATE TABLE `course_set_v8` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `type` varchar(32) NOT NULL DEFAULT '',
                  `title` varchar(1024) DEFAULT '',
                  `subtitle` varchar(1024) DEFAULT '',
                  `tags` text,
                  `categoryId` int(10) NOT NULL DEFAULT '0',
                  `summary` TEXT,
                  `goals` TEXT,
                  `audiences` TEXT,
                  `isVip` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否是VIP课程',
                  `cover` VARCHAR(1024),
                  `status` varchar(32) DEFAULT '0' COMMENT 'draft, published, closed',
                  `creator` int(11) DEFAULT '0',
                  `createdTime` INT(11) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
                  `updatedTime` INT(11) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
                  `serializeMode` varchar(32) NOT NULL DEFAULT 'none' COMMENT 'none, serilized, finished',
                  `ratingNum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程评论数',
                  `rating` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程评分',
                  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程笔记数',
                  `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程学员数',
                  `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
                  `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
                  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
                  `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
                  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
                  `discountId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT '折扣活动ID',
                  `discount` FLOAT( 10, 2 ) NOT NULL DEFAULT  '10' COMMENT  '折扣',
                  `hitNum` int(10) unsigned NOT NULL DEFAULT  '0' COMMENT '课程点击数',
                  `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比',
                  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
                  `parentId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '是否班级课程',
                  `locked` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否锁住',
                  `minCoursePrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最低价格',
                  `maxCoursePrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最高价格',
                  `teacherIds` varchar(1024) DEFAULT null,
                  `defaultCourseId` int(11) unsigned DEFAULT 0 COMMENT '默认的计划ID',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

            $result = $this->getConnection()->exec($sql);
        }

        if (!$this->isFieldExist('course_set_v8', 'defaultCourseId')) {
            $this->exec("ALTER TABLE `course_set_v8` ADD COLUMN `defaultCourseId` int(11) unsigned DEFAULT 0 COMMENT '默认的计划ID';");
        }
        $nextPage = $this->insertCourseSet($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }
    }

    private function updateCourseSet()
    {
        $sql = "UPDATE `course_set_v8` ce, (SELECT count(id) AS num , courseId FROM `course_material` WHERE  source ='coursematerial' AND lessonId >0 GROUP BY courseId) cm  SET ce.`materialNum` = cm.`num`  WHERE ce.`id` = cm.`courseId`;";
        $this->getConnection()->exec($sql);
    }

    private function insertCourseSet($page)
    {
        $countSql = 'SELECT count(*) FROM `course` where `id` not in (select `id` from `course_set_v8`)';
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $sql = "INSERT INTO `course_set_v8` (
            `id`
            ,`title`
            ,`subtitle`
            ,`tags`
            ,`status`
            ,`type`
            ,`serializeMode`
            ,`rating`
            ,`ratingNum`
            ,`categoryId`
            ,`goals`
            ,`audiences`
            ,`isVip`
            ,`recommended`
            ,`recommendedSeq`
            ,`recommendedTime`
            ,`studentNum`
            ,`hitNum`
            ,`discountId`
            ,`discount`
            ,`createdTime`
            ,`updatedTime`
            ,`parentId`
            ,`noteNum`
            ,`locked`
            ,`maxRate`
            ,`orgId`
            ,`orgCode`
            ,`cover`
            ,`creator`
            ,`summary`
            ,`teacherIds`
            ,`defaultCourseId`
            ,`minCoursePrice`
            ,`maxCoursePrice`
        ) SELECT
            `id`
            ,`title`
            ,`subtitle`
            ,`tags`
            ,`status`
            ,`type`
            ,(case when `serializeMode` = 'serialize' then 'serialized' else `serializeMode` end)
            ,`rating`
            ,`ratingNum`
            ,`categoryId`
            ,`goals`
            ,`audiences`
            ,(case when `vipLevelId` > 0 then 1 else 0 end)
            ,`recommended`
            ,`recommendedSeq`
            ,`recommendedTime`
            ,`studentNum`
            ,`hitNum`
            ,`discountId`
            ,`discount`
            ,`createdTime`
            ,`updatedTime`
            ,`parentId`
            ,`noteNum`
            ,`locked`
            ,`maxRate`
            ,`orgId`
            ,`orgCode`
            ,concat('{\"large\":\"',largePicture,'\",\"middle\":\"',middlePicture,'\",\"small\":\"',smallPicture,'\"}') as cover
            ,`userId`
            ,`about`
            ,`teacherIds`
            ,`id`
            ,`price`
            ,`price`
        FROM `course` where `id` not in (select `id` from `course_set_v8`) order by id limit 0, {$this->perPageCount};";

        $result = $this->getConnection()->exec($sql);

        return $page + 1;
    }
}
