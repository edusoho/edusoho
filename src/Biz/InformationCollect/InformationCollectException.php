<?php

namespace Biz\InformationCollect;

use AppBundle\Common\Exception\AbstractException;

class InformationCollectionException extends AbstractException
{
    const EXCEPTION_MODULE = 78;

    const NOTFOUND_COLLECTION = 4047801;

    public $messages = [
        4047801 => 'exception.information_collect.not_found',
    ];
}
