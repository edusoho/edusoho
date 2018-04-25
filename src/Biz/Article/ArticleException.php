<?php

namespace Biz\Article;

use AppBundle\Common\Exception\AbstractException;

class ArticleException extends AbstractException
{
    const MODUAL = 02;

    const NOTFOUND = 4040201;

    public $messages = array(
        4040201 => 'exception.article.notfound',
    );
}
