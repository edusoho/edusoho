<?php
/**
 * User: retamia
 * Date: 2016/10/21
 * Time: 10:30
 */

namespace Biz\Activity\Listener;


use Codeages\Biz\Framework\Context\Biz;

abstract class Listener
{
    /**
     * @var Biz
     */
    private $biz;


    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public abstract function handle($activity, $data);

    /**
     * @return Biz
     */
    protected final function getBiz()
    {
        return $this->biz;
    }

}