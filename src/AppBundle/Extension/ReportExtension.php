<?php

namespace AppBundle\Extension;

use Biz\AuditCenter\ReportSources\GoodsReview;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ReportExtension extends Extension implements ServiceProviderInterface
{
    public function register(Container $container)
    {
    }

    public function getReportSources()
    {
        return [
            'course_review' => GoodsReview::class,
            'classroom_review' => GoodsReview::class,
        ];
    }
}
