<?php

namespace Biz\InformationCollect;

use AppBundle\Common\Exception\AbstractException;

class InformationCollectException extends AbstractException
{
    const EXCEPTION_MODULE = 78;

    const NOTFOUND_COLLECTION = 4047801;

    const COLLECTION_IS_CLOSE = 5007802;

    public $messages = [
        4047801 => 'exception.information_collect.not_found',
        5007802 => 'exception.information_collect.closed',
    ];
}
