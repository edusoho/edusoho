<?php

namespace Biz\Sms\SmsProcessor;

use Codeages\Biz\Framework\Context\Biz;

abstract class BaseSmsProcessor
{
    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function getUrls($targetId, $smsType);

    abstract public function getSmsInfo($targetId, $index, $smsType);

    abstract public function getSmsParams($targetId, $smsType);

    abstract public function getSmcUserIds($targetId, $smsType, $start, $limit);

    abstract public function getSmsUserCount($targetId, $smsType);

    protected function getBiz()
    {
        return $this->biz;
    }
}
