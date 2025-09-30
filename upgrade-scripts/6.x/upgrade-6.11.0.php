<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->getConnection()->commit();

            $this->updateCrontabSetting();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('Crontab.CrontabService')->setNextExcutedTime(time());
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();

        if ($this->isFieldExist('orders', 'payment')) {
            $connection->exec(" ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin','wxpay','heepay','quickpay') CHARACTER SET utf8  NOT NULL");
        }

        if ($this->isFieldExist('cash_flow', 'payment')) {
            $connection->exec(" ALTER TABLE `cash_orders` CHANGE `payment` `payment` ENUM('none','alipay','wxpay','heepay','quickpay') CHARACTER SET utf8 NOT NULL");
        }

        if ($this->isFieldExist('cash_orders', 'payment')) {
            $connection->exec("ALTER TABLE `cash_flow` CHANGE `payment` `payment` ENUM('alipay','wxpay','heepay','quickpay') CHARACTER SET utf8  NULL DEFAULT NULL");
        }

        if (!$this->isFieldExist('orders', 'token')) {
            $connection->exec("ALTER TABLE `orders` ADD `token` VARCHAR(50) NULL DEFAULT NULL COMMENT '令牌'");
        }

        if (!$this->isTableExist("user_pay_agreement")) {
            $connection->exec("
                CREATE TABLE IF NOT EXISTS `user_pay_agreement` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `userId` int(11) NOT NULL COMMENT '用户Id',
                `type` int(8) NOT NULL DEFAULT '0' COMMENT '0:储蓄卡1:信用卡',
                `bankName` varchar(255) NOT NULL COMMENT '银行名称',
                `bankNumber` int(8) NOT NULL COMMENT '银行卡号',
                `userAuth` varchar(225) DEFAULT NULL COMMENT '用户授权',
                `bankAuth` varchar(225) NOT NULL COMMENT '银行授权码',
                `otherId` int(8) NOT NULL COMMENT '对应的银行Id',
                `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户授权银行'"
            );
        }

        $connection->exec("DELETE FROM `theme_config` where `name`='简墨';");
        $connection->exec("INSERT INTO `theme_config`  (`id`, `name`, `config`, `confirmConfig`, `allConfig`, `updatedTime`, `createdTime`, `updatedUserId`)
            VALUES (
                NULL,
                '简墨',
                '{\"maincolor\":\"default\",\"navigationcolor\":\"default\",\"blocks\":{\"left\":[{\"title\":\"\",\"count\":\"12\",\"orderBy\":\"latest\",\"categoryId\":0,\"code\":\"course-grid-with-condition-index\",\"categoryCount\":\"4\",\"defaultTitle\":\"网校课程\",\"subTitle\":\"\",\"defaultSubTitle\":\"精选网校课程，满足你的学习兴趣。\",\"id\":\"latestCourse\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"live-course\",\"defaultTitle\":\"近期直播\",\"subTitle\":\"\",\"defaultSubTitle\":\"实时跟踪直播课程，避免课程遗漏。\",\"id\":\"RecentLiveCourses\"},{\"title\":\"\",\"count\":\"\",\"code\":\"middle-banner\",\"defaultTitle\":\"中部banner\",\"id\":\"middle-banner\"},{\"title\":\"\",\"count\":\"4\",\"code\":\"recommend-classroom\",\"defaultTitle\":\"推荐班级\",\"subTitle\":\"\",\"defaultSubTitle\":\"班级化学习体系，给你更多的课程相关服务。\",\"id\":\"RecommendClassrooms\"},{\"title\":\"\",\"count\":\"6\",\"code\":\"groups\",\"defaultTitle\":\"动态\",\"subTitle\":\"\",\"defaultSubTitle\":\"参与小组，结交更多同学，关注课程动态。\",\"select1\":\"checked\",\"select2\":\"checked\",\"select3\":\"\",\"select4\":\"\",\"id\":\"hotGroups\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"recommend-teacher\",\"defaultTitle\":\"推荐教师\",\"subTitle\":\"\",\"defaultSubTitle\":\"名师汇集，保证教学质量与学习效果。\",\"id\":\"RecommendTeachers\"}]},\"bottom\":\"simple\"}',
                '{\"maincolor\":\"default\",\"navigationcolor\":\"default\",\"blocks\":{\"left\":[{\"title\":\"\",\"count\":\"12\",\"orderBy\":\"latest\",\"categoryId\":0,\"code\":\"course-grid-with-condition-index\",\"categoryCount\":\"4\",\"defaultTitle\":\"网校课程\",\"subTitle\":\"\",\"defaultSubTitle\":\"精选网校课程，满足你的学习兴趣。\",\"id\":\"latestCourse\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"live-course\",\"defaultTitle\":\"近期直播\",\"subTitle\":\"\",\"defaultSubTitle\":\"实时跟踪直播课程，避免课程遗漏。\",\"id\":\"RecentLiveCourses\"},{\"title\":\"\",\"count\":\"\",\"code\":\"middle-banner\",\"defaultTitle\":\"中部banner\",\"id\":\"middle-banner\"},{\"title\":\"\",\"count\":\"4\",\"code\":\"recommend-classroom\",\"defaultTitle\":\"推荐班级\",\"subTitle\":\"\",\"defaultSubTitle\":\"班级化学习体系，给你更多的课程相关服务。\",\"id\":\"RecommendClassrooms\"},{\"title\":\"\",\"count\":\"6\",\"code\":\"groups\",\"defaultTitle\":\"动态\",\"subTitle\":\"\",\"defaultSubTitle\":\"参与小组，结交更多同学，关注课程动态。\",\"select1\":\"checked\",\"select2\":\"checked\",\"select3\":\"\",\"select4\":\"\",\"id\":\"hotGroups\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"recommend-teacher\",\"defaultTitle\":\"推荐教师\",\"subTitle\":\"\",\"defaultSubTitle\":\"名师汇集，保证教学质量与学习效果。\",\"id\":\"RecommendTeachers\"}]},\"bottom\":\"simple\"}',
                NULL,
                '1449218369',
                '1449218369',
                '1'
            );"
        );
    }

    protected function getTheme($uri)
    {
        if (empty($uri)) {
            return;
        }

        $dir = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../web/themes';

        $metaPath = $dir.'/'.$uri.'/theme.json';

        if (!file_exists($metaPath)) {
            return;
        }

        $theme = json_decode(file_get_contents($metaPath), true);

        if (empty($theme)) {
            return;
        }

        $theme['uri'] = $uri;

        return $theme;
    }

    private function updateCrontabSetting()
    {
        $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../app/data/crontab_config.yml");
        $filesystem = new Filesystem();

        if (!empty($dir)) {
            $filesystem->remove($dir);
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
