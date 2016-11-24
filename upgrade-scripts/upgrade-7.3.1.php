<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        if ($index <= 9) {
            $this->getConnection()->beginTransaction();
            try {
                $migration = new TagDataMigration($this->getConnection());

                if ($index == 0) {
                    $this->updateScheme();
                }

                $migration->exec($index);

                if ($index == 8) {
                    $this->migrateCategroy();
                }

                if ($index == 9) {
                    $this->updateRole();
                }
                $this->getConnection()->commit();

                return array('index' => ++$index, 'message' => '正在升级数据库', 'progress' => 0);
            } catch (\Exception $e) {
                $this->getConnection()->rollback();
                throw $e;
            }
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
                    `ownerId` int(10) NOT NULL DEFAULT 0 COMMENT '标签拥有者id',
                    `tagId` int(10) NOT NULL DEFAULT 0 COMMENT '标签id',
                    `userId` int(10) NOT NULL DEFAULT 0 COMMENT '操作用户id',
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

    protected function getCategoryService()
    {
        return ServiceKernel::instance()->createService('Taxonomy.CategoryService');
    }

    protected function getRoleService()
    {
        return ServiceKernel::instance()->createService('Permission:Role.RoleService');
    }

    protected function getRoleDao()
    {
        return ServiceKernel::instance()->createService('Permission:Role.RoleDao');
    }

    protected function updateRole()
    {
        $role = $this->getRoleService()->getRoleByCode('ROLE_ADMIN');
        file_put_contents('log.txt', 'before update ROLE_ADMIN'." {$role['id']} \n",FILE_APPEND);
        if (!in_array('admin_homepage', $role['data'])) {
            $role['data'][] = 'admin_homepage';
            file_put_contents('log.txt',json_encode($role['data'])."\n",FILE_APPEND);
            $this->getRoleDao()->updateRole($role['id'], array('data' => $role['data']));
            file_put_contents('log.txt', 'after update ROLE_ADMIN'."\n",FILE_APPEND);
        }

        $role = $this->getRoleService()->getRoleByCode('ROLE_SUPER_ADMIN');
        file_put_contents('log.txt', 'before update ROLE_SUPER_ADMIN'." {$role['id']} \n",FILE_APPEND);
        if (!in_array('admin_homepage', $role['data'])) {
            $role['data'][] = 'admin_homepage';
            file_put_contents('log.txt', json_encode($role['data'])."\n",FILE_APPEND);
            $this->getRoleDao()->updateRole($role['id'], array('data' => $role['data']));
            file_put_contents('log.txt', 'after update ROLE_SUPER_ADMIN'."\n",FILE_APPEND);
        }
    }

    protected function migrateCategroy()
    {
        //创建班级分类
        $classroomGroup = $this->getCategoryService()->getGroupByCode('classroom');
        if (empty($classroomGroup)) {
            $classroomGroup = $this->getCategoryService()->addGroup(array(
                'name'  => '班级分类',
                'code'  => 'classroom',
                'depth' => 3
            ));
        }

        //复制一级课程分类到班级分类
        $group = $this->getCategoryService()->getGroupByCode('course');
        $firstLevelCategorys = $this->getCategoryService()->findCategoriesByGroupIdAndParentId($group['id'], 0);

        //处理一级
        foreach ($firstLevelCategorys as $firstCategory) {
            $clsFirstCategory = $this->copyCategory($classroomGroup['id'], $firstCategory, 0);
            $twoLevelCategorys = $this->getCategoryService()->findCategoriesByGroupIdAndParentId($group['id'], $firstCategory['id']);
            //处理二级
            foreach ($twoLevelCategorys as $twoLevelCategory) {
                $clsTwoCategory = $this->copyCategory($classroomGroup['id'], $twoLevelCategory, $clsFirstCategory['id']);
                $thirdLevelCategorys = $this->getCategoryService()->findCategoriesByGroupIdAndParentId($group['id'], $twoLevelCategory['id']);
                //处理三级
                foreach ($thirdLevelCategorys as $thirdLevelCategory) {
                    $this->copyCategory($classroomGroup['id'], $thirdLevelCategory, $clsTwoCategory['id']);
                }
            }
        }

    }

    protected function copyCategory($groupId, $category, $parentId)
    {
        $code = 'classroom'.$category['code'];
        $classroomCategory = $this->getCategoryService()->getCategoryByCode($code);
        if (!$classroomCategory) {
            $classroomCategory = $this->getCategoryService()->createCategory(array(
                'name'     => $category['name'],
                'code'     => $code,
                'weight'   => $category['weight'],
                'groupId'  => $groupId,
                'parentId' => $parentId
            ));
        }

        $this->getConnection()->exec('UPDATE classroom SET categoryId = '.$classroomCategory['id'].' WHERE categoryId = '.$category['id']);

        return $classroomCategory;
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
        1 => array('content', 'tagIds'),
        2 => array('article', 'tagIds'),
        3 => array('course', 'tags'),
        4 => array('open_course', 'tags'),
        5 => array('course_lesson', 'tags'),
        6 => array('open_course_lesson', 'tags'),
        7 => array('classroom', 'tags')
    );

    protected $ownerType = array(
        'content'            => 'content',
        'article'            => 'article',
        'course'             => 'course',
        'course_lesson'      => 'course_lesson',
        'open_course_lesson' => 'open_course_lesson',
        'open_course'        => 'openCourse',
        'classroom'          => 'classroom'
    );

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function exec($index)
    {
        if (empty($this->columns[$index])) {
            return;
        }
        $table  = $this->columns[$index][0];
        $column = $this->columns[$index][1];

        $this->migration($table, $column);
    }

    protected function migration($table, $column)
    {
        $sql = "SELECT * FROM {$table}";

        $targets = $this->connection->fetchAll($sql, array()) ?: array();

        foreach ($targets as $target) {
            if (!empty($target[$column])) {
                $tags = $this->unserialize($target[$column]);
                $headTeacherId = empty($target['headTeacherId']) ? 0:$target['headTeacherId'];
                $userId = empty($target['userId']) ? $headTeacherId: $target['userId'];
                $fields = array(
                    'userId'    => empty($userId) ? 0:$userId,
                    'tags'      => $tags,
                    'ownerType' => $this->ownerType[$table],
                    'ownerId'   => $target['id']
                );

                $this->moveTagData($fields);
            }
        }

    }

    protected function moveTagData($fields)
    {
        $fields['tags'] = array_filter($fields['tags']);
        foreach ($fields['tags'] as $tag) {
            $exist = $this->getTagService()->getTagOwnerRelationByTagIdAndOwner($tag, array('ownerType' => $fields['ownerType'], 'ownerId' => $fields['ownerId']));
            if (!$exist) {
                $this->getTagService()->addTagOwnerRelation(array(
                    'ownerType'   => $fields['ownerType'],
                    'ownerId'     => $fields['ownerId'],
                    'tagId'       => $tag,
                    'userId'      => $fields['userId'],
                    'createdTime' => time()
                ));
            }
            
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
