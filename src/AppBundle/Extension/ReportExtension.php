<?php

namespace AppBundle\Extension;

use Biz\AuditCenter\ReportSources\CommonReview;
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
            'course_review' => CommonReview::class,
            'course_review_reply' => CommonReview::class,
            'classroom_review' => CommonReview::class,
            'classroom_review_reply' => CommonReview::class,
        ];
    }
}
