<?php

namespace Biz\System\Template;

use Biz\Content\Service\BlockService;
use Codeages\Biz\Framework\Context\Biz;

class TemplateFactory
{
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function getTemplateClass($template)
    {
        $class = __NAMESPACE__.'\\'.ucfirst($template).'Template';

        return new $class($this->biz);
    }
}
