<?php

namespace Biz\Announcement\Processor;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class AnnouncementProcessorFactory
{
    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @param $target
     *
     * @return AnnouncementProcessor
     *
     * @throws InvalidArgumentException
     */
    public function create($target)
    {
        if (empty($target) || !in_array($target, array('course', 'classroom'))) {
            throw new InvalidArgumentException('公告类型不存在');
        }

        $class = __NAMESPACE__.'\\'.ucfirst($target).'AnnouncementProcessor';

        return new $class($this->biz);
    }
}
