<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $migration = new TagDataMigration($this->getConnection());

            $migration->exec($index);

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir') . "../web/install");
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

    private function updateScheme()
    {
        $connection = $this->getConnection();

        if (!$this->isTableExist('tag_owner')) {
            $connection->exec("
                CREATE TABLE `tag_owner` (
                    `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签ID',
                    `ownerType` varchar(255) NOT NULL DEFAULT '' COMMENT '标签拥有者类型',
                    `ownerId` int(10) NOT NULL DEFAULT '0' COMMENT '标签拥有者id',
                    `tagId` int(10) NOT NULL DEFAULT '0' COMMENT '标签id',
                    `userId` int(10) NOT NULL DEFAULT '' COMMENT '操作用户id',
                    `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签关系表';
            ");
        }

        if (!$this->isTableExist('tag_group')) {
            $connection->exec("
                CREATE TABLE `tag_group` (
                    `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签ID',
                    `name` varchar(255) NOT NULL DEFAULT '' COMMENT '标签组名字',
                    `scope` varchar(255) NOT NULL DEFAULT '' COMMENT '标签组应用范围',
                    `tagNum` int(10) NOT NULL DEFAULT '0' COMMENT '标签组里的标签数量',
                    `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
                    `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签组表';
            ");
        }

        if (!$this->isTableExist('tag_group_tag')) {
            $connection->exec("
                CREATE TABLE `tag_group_tag` (
                    `id` int(10) NOT NULL AUTO_INCREMENT,
                    `tagId` int(10) NOT NULL DEFAULT '0' COMMENT '标签ID',
                    `groupId` int(10) NOT NULL DEFAULT '0' COMMENT '标签组ID',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签组跟标签的中间表';
            ");
        }
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

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql    = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql    = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * @return \Topxia\Service\System\Impl\SettingServiceImpl
     */
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

    /**
     * @return \Topxia\Service\Common\Connection
     */
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

class TagDataMigration
{
    protected $connection;

    protected $columns = array(
        0 => array('content', 'tagIds'),
        1 => array('article', 'tagIds'),
        2 => array('course', 'tags'),
        3 => array('open_course', 'tags'),
        4 => array('course_lesson', 'tags'),
        5 => array('open_course_lesson', 'tags'),
        6 => array('classroom', 'tags')
    );

    protected $ownerType = array(
        'article'            => 'article',
        'course'             => 'course',
        'open_course'        => 'openCourse',
        'classroom'          => 'classroom'
    );

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function exec($index)
    {   
        $table  = $this->columns[$index][0];
        $column = $this->columns[$index][1];

        $this->migration($table, $column);
    }

    protected function migration($table, $column)
    {
        if ($table != 'content' && $table != 'course_lesson' && $table != 'open_course_lesson') {
            $sql = "SELECT * FROM {$table}";

            $targets = $this->connection->fetchAll($sql, array()) ?: array();

            foreach ($targets as $target) {
                if (!empty($target[$column])) {
                    $tags = $this->unserialize($target[$column]);

                    $fields = array(
                        'userId'    => empty($target['userId']) ? '' : $target['userId'],
                        'tags'      => $tags,
                        'ownerType' => $this->ownerType[$table],
                        'ownerId'   => $target['id']
                    );

                    $this->moveTagData($fields);
                }
            }
        }

        $this->dropTagField($table);
    }

    protected function dropTagField($table)
    {   
        $sql = "ALTER TABLE {$table} DROP COLUMN {$this->columns[$table]}";
        $this->connection->exec($sql);
    }

    protected function moveTagData($fields)
    {   
        $fields['tags'] = array_filter($fields['tags']);
        foreach ($fields['tags'] as $tag) {
            if (!empty($fields['userId'])) {
                $this->getTagService()->addTagOwnerRelation(array(
                    'ownerType'   => $fields['ownerType'],
                    'ownerId'     => $fields['ownerId'],
                    'tagId'       => $tag,
                    'userId'      => $fields['userId'],
                    'createdTime' => time()
                ));
                continue;
            }

            $this->getTagService()->addTagOwnerRelation(array(
                'ownerType'   => $fields['ownerType'],
                'ownerId'     => $fields['ownerId'],
                'tagId'       => $tag,
                'createdTime' => time()
            ));
        }
    }

    protected function unSerialize($tags)
    {
        if (empty($tags)) {
            return array();
        }

        return explode('|', $tags);
    }

    protected function getTagService()
    {
        return ServiceKernel::instance()->createService('Taxonomy.TagService');
    }
}
