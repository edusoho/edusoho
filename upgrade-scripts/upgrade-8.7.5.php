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
            $dir = realpath($this->biz['kernel.root_dir'].'/../web/install');
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
            'dropUnusedTables',
            'addGoodsTable',
            'addGoodsSpecsTable',
            'addProductTable',
            'addMarketingMeansTable',
            'addGoodsPurchaseTable',
            'deleteTaskWithNullChapter',
            'alterClassroomTeacherIds',
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

    public function dropUnusedTables()
    {
        $this->getConnection()->exec("
            DROP TABLE IF EXISTS `course`;
            DROP TABLE IF EXISTS `course_material`;
            DROP TABLE IF EXISTS `testpaper`;
            DROP TABLE IF EXISTS `testpaper_item`;
            DROP TABLE IF EXISTS `testpaper_result`;
            DROP TABLE IF EXISTS `testpaper_item_result`;
        ");
        $this->logger('info', '删除7.0以来不需要的表');
        return 1;
    }

    public function addGoodsTable()
    {
        if (!$this->isTableExist('goods')) {
            $this->getConnection()->exec("
            CREATE TABLE `goods` (
               `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
               `productId` int(10) unsigned NOT NULL COMMENT '产品id',
               `title` varchar(1024) NOT NULL COMMENT '商品标题',
               `images` text DEFAULT NULL COMMENT '商品图片',
               `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
               `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
               PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品表';
            ");
        }

        return 1;
    }

    public function addGoodsSpecsTable()
    {
        if (!$this->isTableExist('goods_specs')) {
            $this->getConnection()->exec("
            CREATE TABLE `goods_specs` (
               `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
               `goodsId` int(10) unsigned NOT NULL COMMENT '商品id',
               `targetId` int(10) unsigned NOT NULL COMMENT '目标内容Id,如教学计划id',
               `title` varchar(1024) NOT NULL COMMENT '规格标题',
               `images` text DEFAULT NULL COMMENT '商品图片',
               `price` float(10,2) NOT NULL DEFAULT 0.00 COMMENT '价格',
               `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
               `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
               PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品规格表';
            ");
        }
        return 1;
    }

    public function addProductTable()
    {
        if (!$this->isTableExist('product')) {
            $this->getConnection()->exec("
            CREATE TABLE `product` (
               `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
               `targetType` varchar (32) NOT NULL COMMENT '产品类型,course、classroom、lesson、open_course ...',
               `targetId` int(10) unsigned NOT NULL COMMENT '对应产品资源id',
               `title` varchar(1024) NOT NULL COMMENT '产品名称',
               `owner` int(10) unsigned NOT NULL COMMENT '拥有者（创建者）',
               `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
               `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
               PRIMARY KEY (`id`),
               KEY `targetType_targetId` (targetType, targetId)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品表';
            ");
        }

        return 1;
    }

    public function addMarketingMeansTable()
    {
        if (!$this->isTableExist('marketing_means')) {
            $this->getConnection()->exec("
                CREATE TABLE `marketing_means` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `type` varchar(64) NOT NULL DEFAULT '' COMMENT '营销手段：discount,vip,coupon',
                  `fromMeansId` int(10) unsigned NOT NULL COMMENT '对应营销手段id（discountId, couponBatchId,vipLevelId）',
                  `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '目标类型:整个商品、单个规格；goods,specs,category(商品分类)',
                  `targetId` int(10) unsigned NOT NULL COMMENT '单个目标，如果批量选，则创建多条，0：表示某目标类型的所有，>0: 表示具体对应的目标类型',
                  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '1： 有效， 2：无效',
                  `visibleOnGoodsPage` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '商品页可用,  0： 不可用 1 可用 （业务需求，字段暂时启用）',
                  `createdTime` int(11) unsigned NOT NULL,
                  `updatedTime` int(11) unsigned NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `targetType_targetId` (`targetType`,`targetId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    public function addGoodsPurchaseTable()
    {
        if (!$this->isTableExist('goods_purchase_voucher')) {
            $this->getConnection()->exec("
            CREATE TABLE `goods_purchase_voucher` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `specsId` int(11) unsigned NOT NULL,
                  `goodsId` int(11) unsigned NOT NULL,
                  `orderId` int(11) unsigned NOT NULL DEFAULT '0',
                  `userId` int(11) unsigned NOT NULL,
                  `effectiveType` varchar(64) NOT NULL COMMENT '生效类型',
                  `effectiveTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '生效时间',
                  `invalidTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '失效时间',
                  `createdTime` int(11) unsigned NOT NULL DEFAULT '0',
                  `updatedTime` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }
        return 1;
    }

    public function deleteTaskWithNullChapter()
    {
        $sql = 'SELECT course_task.id AS id,course_task.title AS title,course_task.categoryId AS chapterId FROM course_task LEFT JOIN course_chapter ON course_task.categoryId = course_chapter.id WHERE course_chapter.id IS NULL;';
        $shouldDelete = $this->getConnection()->fetchAll($sql, array());
        $this->logger('info', json_encode($shouldDelete));

        $taskIds = ArrayToolkit::column($shouldDelete, 'id');
        $marks = str_repeat('?,', count($taskIds) - 1).'?';
        $sql = "DELETE from course_task where id in ({$marks});";
        $this->getConnection()->executeUpdate($sql, $taskIds);

        return 1;
    }

    public function alterClassroomTeacherIds()
    {
        if ($this->isFieldExist('classroom', 'teacherIds')) {
            $this->getConnection()->exec("ALTER TABLE `classroom` modify COLUMN `teacherIds` varchar(1024) NOT NULL DEFAULT '' COMMENT '教师IDs';");
        }

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
        $data = date('Y-m-d H:i:s')." [{$level}] {$version} ".$message.PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'].'/../app/logs/upgrade.log';
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
