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

    abstract function getUrls($targetId, $smsType);

	abstract function getSmsInfo($targetId, $index, $smsType);

    protected function getBiz()
    {
        return $this->biz;
    }
}