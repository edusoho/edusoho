<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme($index);

            $this->getConnection()->commit();

            if (!empty($result)) {
                return $result;
            } else {
                $this->logger('info', '执行升级脚本结束');
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            $this->logger('error', $e->getTraceAsString());
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . '/../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger('error', $e->getTraceAsString());
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'createReviewTable',
            'processCourseReview',
            'processClassroomReview',
            'createFavoriteTable',
            'processCourseFavorite',
            'processThreadFavorite'
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');
            $this->deleteCache();

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if (1 == $page) {
            ++$step;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }
    }

    public function createReviewTable()
    {
        $this->logger('info', '创建评价表');

        if (!$this->isTableExist('review')) {
            $this->getConnection()->exec("
                 CREATE TABLE `review` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评价人',
                    `targetType` varchar(64) NOT NULL COMMENT '评论的对象类型',
                    `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论对象id',
                    `content` text NOT NULL COMMENT '评论内容',
                    `rating` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分',
                    `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复id',
                    `meta` text COMMENT '评论元信息',
                    `createdTime` int(10) unsigned NOT NULL COMMENT '评价创建时间',
                    `updatedTime` int(10) unsigned NOT NULL COMMENT '评价更新时间',
                    PRIMARY KEY (`id`),
                    KEY `targetType_targetId` (targetType, targetId)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评价表';
            ");
        }

        return 1;
    }

    public function processCourseReview($page)
    {
        $this->logger('info', '处理课程评价');

        $this->getConnection()->exec("
             DELETE FROM `review` WHERE targetType='course';
        ");

        $this->logger('info', '开始迁移课程评价');
        $this->getConnection()->exec("
            INSERT INTO `review` (userId, targetType, targetId, content, rating, parentId, meta, createdTime, updatedTime)
             SELECT userId, 'course' as targetType, courseId as targetId, content, rating, parentId, meta, createdTime, updatedTime from course_review;
        ");
        $this->logger('info', '迁移课程评价完成');

        return 1;
    }

    public function processClassroomReview($page)
    {
        $this->logger('info', '处理班级评价');

        $this->getConnection()->exec("
             DELETE FROM `review` WHERE targetType='classroom';
        ");

        $this->logger('info', '开始迁移班级评价');

        $this->getConnection()->exec("
            INSERT INTO `review` (userId, targetType, targetId, content, rating, parentId, meta, createdTime, updatedTime)
             SELECT userId, 'classroom' as targetType, classroomId as targetId, content, rating, parentId, meta, createdTime, updatedTime from classroom_review;
        ");

        $this->logger('info', '迁移班级评价完成');

        return 1;
    }

    public function createFavoriteTable($page)
    {
        if (!$this->isTableExist('favorite')) {
            $this->getConnection()->exec("
                CREATE TABLE `favorite` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏人',
                    `targetType` varchar(64) NOT NULL COMMENT '收藏的对象类型',
                    `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏对象id',
                    `createdTime` int(10) unsigned NOT NULL COMMENT '收藏时间',
                    PRIMARY KEY (`id`),
                    KEY `targetType_targetId` (targetType, targetId),
                    KEY `userId` (userId)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='收藏表';
            ");
        }

        return 1;
    }

    public function processCourseFavorite($page)
    {
        $this->logger('info', '处理课程收藏');

        $this->getConnection()->exec("
             DELETE FROM `favorite` WHERE targetType IN ('course','openCourse');
        ");

        $this->logger('info', '开始迁移课程收藏');
        $this->getConnection()->exec("
            INSERT INTO `favorite` (userId, targetType, targetId, createdTime)
             SELECT userId, `type` as targetType, courseId as targetId, createdTime from course_favorite;
        ");
        $this->logger('info', '迁移课程收藏完成');

        return 1;
    }


    public function processThreadFavorite($page)
    {
        $this->logger('info', '处理话题收藏');

        $this->getConnection()->exec("
             DELETE FROM `favorite` WHERE targetType='thread';
        ");

        $this->logger('info', '开始迁移话题收藏');
        $this->getConnection()->exec("
            INSERT INTO `favorite` (userId, targetType, targetId, createdTime)
             SELECT userId, 'thread' as targetType, threadId as targetId, createdTime from groups_thread_collect;
        ");
        $this->logger('info', '迁移话题收藏完成');

        return 1;
    }

    protected function generateIndex($step, $page)
    {
        return $step * 1000000 + $page;
    }

    protected function getStepAndPage($index)
    {
        $step = intval($index / 1000000);
        $page = $index % 1000000;

        return array($step, $page);
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
    }

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger('info', '删除缓存');

        return 1;
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getConnection()
    {
        return $this->biz['db'];
    }

    protected function createService($name)
    {
        return $this->biz->service($name);
    }

    protected function createDao($name)
    {
        return $this->biz->dao($name);
    }

    abstract public function update();

    protected function logger($level, $message)
    {
        $version = \AppBundle\System::VERSION;
        $data = date('Y-m-d H:i:s') . " [{$level}] {$version} " . $message . PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'] . '/../app/logs/upgrade.log';
    }

    /**
     * @return \Biz\DiscoveryColumn\Service\DiscoveryColumnService
     */
    protected function getDiscoveryColumnService()
    {
        return $this->createService('DiscoveryColumn:DiscoveryColumnService');
    }

    /**
     * @return \Biz\Taxonomy\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return \Biz\System\Service\H5SettingService
     */
    protected function getH5SettingService()
    {
        return $this->createService('System:H5SettingService');
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
