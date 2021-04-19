<?php

namespace AppBundle\Extension;

use Biz\AuditCenter\ReportSources\GoodsReview;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ReportExtension extends Extension implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // TODO: Implement register() method.
    }

    /**
     * @return string[]
     *                  简单类暂不使用单例模式
     */
    public function getReportSources()
    {
        return [
            'course_review' => GoodsReview::class,
            'classroom_review' => GoodsReview::class,
        ];
    }
}
