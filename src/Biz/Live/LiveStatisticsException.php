<?php

namespace Biz\Live;

use AppBundle\Common\Exception\AbstractException;

class LiveStatisticsException extends AbstractException
{
    const LIVE_STATISTICS_NOT_FOUND = '4001';

    const LIVE_STATISTICS_NOT_SUPPORT = '4002';

    public $messages = array(
        self::LIVE_STATISTICS_NOT_FOUND => 'exception.live_statistic.not_found',
        self::LIVE_STATISTICS_NOT_SUPPORT => 'exception.live_statistic.not_support',
    );
}
