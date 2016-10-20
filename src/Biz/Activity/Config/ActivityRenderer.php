<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 11:12
 */

namespace Biz\Activity\Config;


use Codeages\Biz\Framework\Context\Biz;

abstract class ActivityRenderer
{
    /**
     * @var Biz
     */
    private $biz;

    public final function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function render($template, $arguments=array())
    {
        global $kernel;
        return $kernel->getContainer()->get('templating')->render($template, $arguments);
    }

    /**
     * @return Biz
     */
    protected final function getBiz()
    {
        return $this->biz;
    }

    public abstract function renderCreating();

    public abstract function renderEditing($activityId);

    public abstract function renderShow($activityId);
}