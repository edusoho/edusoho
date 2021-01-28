<?php

namespace Biz\Announcement\Processor;

use Biz\Announcement\AnnouncementException;
use Codeages\Biz\Framework\Context\Biz;

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
     * @throws AnnouncementException
     */
    public function create($target)
    {
        if (empty($target) || !in_array($target, array('course', 'classroom'))) {
            throw AnnouncementException::TYPE_INVALID();
        }

        $class = __NAMESPACE__.'\\'.ucfirst($target).'AnnouncementProcessor';

        return new $class($this->biz);
    }
}
