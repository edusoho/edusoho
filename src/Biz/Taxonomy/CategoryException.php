<?php

namespace Biz\Taxonomy;

use AppBundle\Common\Exception\AbstractException;

class CategoryException extends AbstractException
{
    const EXCEPTION_MODUAL = 21;

    const NOTFOUND_CATEGORY = 4042101;

    public $messages = array(
        4042101 => 'exception.category.not_found',
    );
}