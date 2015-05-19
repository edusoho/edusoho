<?php

use Symfony\Component\Filesystem\Filesystem;

class UpgradeScript110
{

    protected $kernel;

    protected $version;

    public function __construct ($kernel, $version)
    {
        $this->kernel = $kernel;
        $this->version = $version;
    }

    public function execute()
    {
        $rootDir = realpath($this->kernel->getParameter('kernel.root_dir') . '/../');

        $originDir = "{$rootDir}/plugins/Classroom/ClassroomBundle/Resources/public";
        $targetDir = "{$rootDir}/web/bundles/classroom";

        $filesystem = new Filesystem();

        if ($filesystem->exists($targetDir)) {
            $filesystem->remove($targetDir);
        }

        $filesystem->mirror($originDir, $targetDir, null, array('override' => true, 'delete' => true));
    }

    public function _updateScheme()
    {
        $connection = $this->kernel->getConnection();
        $connection->exec("ALTER TABLE `classroom_member` CHANGE `role` `role` ENUM('auditor','student','teacher','headTeacher','assistant','studentAssistant') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'auditor' COMMENT '角色';");
        $connection->exec("ALTER TABLE `classroom` ADD COLUMN `private` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否封闭班级';");
        $connection->exec("ALTER TABLE `classroom` ADD COLUMN `service` varchar(255) DEFAULT NULL COMMENT '班级服务';");
        $connection->exec("ALTER TABLE `classroom_courses` ADD `disabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否禁用' AFTER `courseId`;");
        $connection->exec("ALTER TABLE `classroom` ADD `noteNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '班级笔记数量' AFTER `threadNum`;");
        $connection->exec("ALTER TABLE `classroom` ADD `categoryId` INT(10) NOT NULL DEFAULT '0' COMMENT '分类id' AFTER `about`;");
        $connection->exec("ALTER TABLE classroom ADD COLUMN `recommended` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐班级';");
        $connection->exec("ALTER TABLE classroom ADD COLUMN `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '推荐序号';");
        $connection->exec("ALTER TABLE classroom ADD COLUMN `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间';");

    }
}
