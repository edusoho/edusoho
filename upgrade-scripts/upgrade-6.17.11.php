<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme($index);
            $this->getConnection()->commit();
            if($result) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    private function updateData($index)
    {

        $total = $this->countCourseLessons();

        $maxPage = ceil($total / 100) ? ceil($total / 100) : 1;
        $page = 100;
        $start = ($index-1)*$page;

        $sql = "select * from course_lesson where type not in('text','live','testpaper') and mediaId != 0 and mediaSource = 'self' order by createdTime limit {$start}, {$page};";

        $lessons = $this->getConnection()->fetchAll($sql, array());
        if ($lessons) {
            foreach ($lessons as $key => $lesson) {
                $sql  = "select id,filename,fileSize from upload_files where id=".$lesson['mediaId'];
                $file = $this->getConnection()->fetchAssoc($sql);

                $materialSql    = "select id from course_material where lessonId=".$lesson['copyId']." and fileId=".$lesson['mediaId']." and source='courselesson';";
                $parentMaterial = $this->getConnection()->fetchAssoc($materialSql);


                if ($file) {
                    
                    $emptyCourseMaterial = $this->emptyCourseMaterial($lesson['courseId'], $lesson['id'], $file['id']);

                    if(!$emptyCourseMaterial) {
                        continue;
                    }

                    $courseId = $lesson['courseId'];
                    $lessonId = $lesson['id'];
                    $title    = $file['filename'];
                    $fileId   = $file['id'];
                    $fileSize = $file['fileSize'];
                    $copyId   = $parentMaterial ? $parentMaterial['id'] : 0;
                    $userId   = $lesson['userId'];
                    $time     = time();

                    $this->getConnection()->exec("insert into course_material (courseId,lessonId,title,fileId,fileSize,source,copyId,userId,createdTime) values({$courseId},{$lessonId},'{$title}',{$fileId},{$fileSize},'courselesson',{$copyId},{$userId},UNIX_TIMESTAMP());");
                }
            }
        }


        if ($index <= $maxPage) {
            return array(
                'index'    => $index + 1,
                'message'  => '正在升级数据...',
                'progress' => 4.4
            );
        }
    }

    protected function emptyCourseMaterial($courseId, $lessonId, $fileId)
    {
        $sql = "select * from course_material where courseId={$courseId} and lessonId=".$lessonId." and fileId=".$fileId." and source='courselesson';";
        $result = $this->getConnection()->fetchAssoc($sql, array());

        return empty($result);
    }

    protected function countCourseLessons()
    {
        $sql     = "select count(*) as total from course_lesson where type not in('text','live','testpaper') and mediaId != 0 and mediaSource = 'self';";

        $count = $this->getConnection()->fetchAssoc($sql, array());

        return $count['total'];
    }

    protected function updateScheme($index)
    {
        if($index > 0){
            return $this->updateData($index);
        }

        if (!$this->isTableExist('org')) {
            $this->getConnection()->exec("CREATE TABLE IF NOT EXISTS  `org` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '组织机构ID',
              `name` varchar(255) NOT NULL COMMENT '名称',
              `parentId` int(11) NOT NULL DEFAULT '0' COMMENT '组织机构父ID',
              `childrenNum` tinyint(3) unsigned NOT NULL  DEFAULT  '0' COMMENT '辖下组织机构数量',
              `depth` int(11) NOT NULL   DEFAULT  '1' COMMENT '当前组织机构层级',
              `seq` int(11) NOT NULL COMMENT '索引',
              `description` text COMMENT '备注',
              `code` varchar(255) NOT NULL DEFAULT '' COMMENT '机构编码',
              `orgCode` varchar(255) NOT NULL DEFAULT '0' COMMENT '内部编码',
              `createdUserId` int(11) NOT NULL COMMENT '创建用户ID',
              `createdTime` int(11) unsigned NOT NULL  COMMENT '创建时间',
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '最后更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `orgCode` (`orgCode`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='组织机构';
            ");

            $this->getConnection()->exec("INSERT INTO `org` (`id`, `name`, `parentId`, `childrenNum`, `depth`, `seq`, `description`, `code`, `orgCode`, `createdUserId`, `createdTime`, `updateTime`) VALUES (1, '全站', 0, 0, 1, 0, '', 'FullSite', '1.', 1, 1463555406, 0);");
        }

        if (!$this->isFieldExist('article', 'orgId')) {
            $this->getConnection()->exec("ALTER TABLE `article` ADD `orgId` INT(10) unsigned NULL DEFAULT '1';");
        }
        if (!$this->isFieldExist('article', 'orgCode')) {
            $this->getConnection()->exec("ALTER TABLE `article` ADD `orgCode` varchar(255)  NULL DEFAULT '1.' comment '组织机构内部编码';");
        }

        if (!$this->isFieldExist('course', 'orgId')) {
            $this->getConnection()->exec("ALTER TABLE `course` ADD `orgId` INT(10) unsigned NULL DEFAULT '1';");
        }
        if (!$this->isFieldExist('course', 'orgCode')) {
            $this->getConnection()->exec("ALTER TABLE `course` ADD `orgCode` varchar(255)  NULL DEFAULT '1.' comment '组织机构内部编码';");
        }

        if (!$this->isFieldExist('classroom', 'orgId')) {
            $this->getConnection()->exec("ALTER TABLE `classroom` ADD `orgId` INT(10) unsigned NULL DEFAULT '1';");
        }
        if (!$this->isFieldExist('classroom', 'orgCode')) {
            $this->getConnection()->exec("ALTER TABLE `classroom` ADD `orgCode` varchar(255)  NULL DEFAULT '1.' comment '组织机构内部编码';");
        }

        if (!$this->isFieldExist('user', 'orgId')) {
            $this->getConnection()->exec("ALTER TABLE `user` ADD `orgId` INT(10) unsigned NULL DEFAULT '1';");
        }
        if (!$this->isFieldExist('user', 'orgCode')) {
            $this->getConnection()->exec("ALTER TABLE `user` ADD `orgCode` varchar(255)  NULL DEFAULT '1.' comment '组织机构内部编码';");
        }

        if (!$this->isFieldExist('announcement', 'orgId')) {
            $this->getConnection()->exec("ALTER TABLE `announcement` ADD `orgId` INT(10) unsigned NULL DEFAULT '1';");
        }
        if (!$this->isFieldExist('announcement', 'orgCode')) {
            $this->getConnection()->exec("ALTER TABLE `announcement` ADD `orgCode` varchar(255)  NULL DEFAULT '1.' comment '组织机构内部编码';");
        }

        if (!$this->isFieldExist('category', 'orgId')) {
            $this->getConnection()->exec("ALTER TABLE `category` ADD `orgId` INT(10) unsigned NULL DEFAULT '1';");
        }
        if (!$this->isFieldExist('category', 'orgCode')) {
            $this->getConnection()->exec("ALTER TABLE `category` ADD `orgCode` varchar(255)  NULL DEFAULT '1.' comment '组织机构内部编码';");
        }

        if (!$this->isFieldExist('tag', 'orgId')) {
            $this->getConnection()->exec("ALTER TABLE `tag` ADD `orgId` INT(10) unsigned NULL DEFAULT '1';");
        }
        if (!$this->isFieldExist('tag', 'orgCode')) {
            $this->getConnection()->exec("ALTER TABLE `tag` ADD `orgCode` varchar(255)  NULL DEFAULT '1.' comment '组织机构内部编码';");
        }

        if (!$this->isFieldExist('navigation', 'orgId')) {
            $this->getConnection()->exec("ALTER TABLE `navigation` ADD `orgId` INT(10) unsigned NULL DEFAULT '1';");
        }
        if (!$this->isFieldExist('navigation', 'orgCode')) {
            $this->getConnection()->exec("ALTER TABLE `navigation` ADD `orgCode` varchar(255)  NULL DEFAULT '1.' comment '组织机构内部编码';");
        }

        if (!$this->isFieldExist('org', 'seq')) {
            $this->getConnection()->exec("ALTER TABLE `org` CHANGE `seq` `seq` INT(11) NOT NULL DEFAULT '0' COMMENT '索引';");
        }

        return array(
            'index'    => 1,
            'message'  => '正在升级数据...',
            'progress' => 4.4
        );
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql    = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $kernel;
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
}
