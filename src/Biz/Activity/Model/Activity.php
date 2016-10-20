<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 11:10
 */

namespace Biz\Activity\Model;


use Biz\Activity\Config\ActivityConfig;
use Codeages\Biz\Framework\Context\Biz;

abstract class Activity
{
    public $name;

    private $biz;

    /**
     * @inheritdoc
     */
    public function create($fields)
    {}

    /**
     * @inheritdoc
     */
    public function update($targetId, $fields)
    {}

    /**
     * @inheritdoc
     */
    public function delete($targetId)
    {}

    /**
     * @inheritdoc
     */
    public function get($targetId)
    {}

    public final function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return ActivityConfig
     */
    public abstract function getConfig();

    public function getBiz()
    {
        return $this->biz;
    }
}