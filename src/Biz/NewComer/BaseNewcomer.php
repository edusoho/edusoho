<?php

namespace Biz\NewComer;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;

class BaseNewcomer
{
    private $biz;

    final public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function getStatus()
    {
        return false;
    }

    public function doneTask($taskName)
    {
        $newcomerTask = $this->getSettingService()->get('newcomer_task', array());
        $newcomerTask = array_merge($newcomerTask, array($taskName => array('status' => 1)));

        return $this->getSettingService()->set('newcomer_task', $newcomerTask);
    }

    /**
     * @return Biz
     */
    final protected function getBiz()
    {
        return $this->biz;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
