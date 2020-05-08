<?php

namespace Biz\S2B2C;

use Biz\S2B2C\Sync\Component\Activity\Audio;
use Biz\S2B2C\Sync\Component\Activity\Discuss;
use Biz\S2B2C\Sync\Component\Activity\Doc;
use Biz\S2B2C\Sync\Component\Activity\Download;
use Biz\S2B2C\Sync\Component\Activity\Exercise;
use Biz\S2B2C\Sync\Component\Activity\Flash;
use Biz\S2B2C\Sync\Component\Activity\Homework;
use Biz\S2B2C\Sync\Component\Activity\Live;
use Biz\S2B2C\Sync\Component\Activity\Ppt;
use Biz\S2B2C\Sync\Component\Activity\Testpaper;
use Biz\S2B2C\Sync\Component\Activity\Text;
use Biz\S2B2C\Sync\Component\Activity\Video;
use Biz\S2B2C\Sync\Component\CourseProductSync;
use Biz\S2B2C\Sync\Component\TaskSync;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Topxia\Service\Common\ServiceKernel;

class S2B2CProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        // 日志
        $biz['s2b2c.merchant.logger'] = function () {
            $logger = new Logger('S2B2CMerchant');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/s2b2c.log', Logger::DEBUG));

            return $logger;
        };

        $biz['s2b2c.merchant.job.logger'] = function () {
            $logger = new Logger('S2B2CMerchantJob');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/crontab.log', Logger::DEBUG));

            return $logger;
        };

        // 接口
        $biz['supplier.platform_api'] = function ($biz) {
            return new SupplierPlatformApi($biz);
        };

        /*
         * @param $biz
         * @return mixed
         * 出现多种Product类型，要设计同步构建工厂
         */
        $biz['s2b2c.course_product_sync'] = function ($biz) {
            $courseProductNodes = $this->generateCourseProductNodes();
            $syncClass = $courseProductNodes['class'];

            return new $syncClass($biz, $courseProductNodes);
        };

        $activities = $this->getActivities();
        foreach ($activities as $type => $activity) {
            $biz['s2b2c.sync_activity_type.'.$type] = function ($biz) use ($activity) {
                return new $activity['syncClass']($biz);
            };
        }
    }

    private function generateCourseProductNodes()
    {
        return [
            'class' => CourseProductSync::class,
            'children' => [
                'task' => [
                    'class' => TaskSync::class,
                ],
            ],
        ];
    }

    private function getActivities()
    {
        return [
            'text' => [
                'syncClass' => Text::class,
            ],
            'video' => [
                'syncClass' => Video::class,
            ],
            'audio' => [
                'syncClass' => Audio::class,
            ],
            'live' => [
                'syncClass' => Live::class,
            ],
            'discuss' => [
                'syncClass' => Discuss::class,
            ],
            'flash' => [
                'syncClass' => Flash::class,
            ],
            'doc' => [
                'syncClass' => Doc::class,
            ],
            'ppt' => [
                'syncClass' => Ppt::class,
            ],
            'testpaper' => [
                'syncClass' => Testpaper::class,
            ],
            'homework' => [
                'syncClass' => Homework::class,
            ],
            'exercise' => [
                'syncClass' => Exercise::class,
            ],
            'download' => [
                'syncClass' => Download::class,
            ],
        ];
    }
}
