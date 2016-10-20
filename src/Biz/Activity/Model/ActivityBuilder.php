<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 13:10
 */

namespace Biz\Activity\Model;


use Codeages\Biz\Framework\Context\Biz;
use Topxia\Common\Exception\UnexpectedValueException;

class ActivityBuilder
{
    /**
     * @var Activity
     */
    private $activity;

    private $activityType;

    private $biz;

    public final static function build(Biz $biz)
    {
        $instance      = new self();
        $instance->biz = $biz;
        return $instance;
    }

    /**
     * @param string $type 活动类型
     * @return $this
     */
    public final function type($type)
    {
        $this->activityType = $type;
        return $this;
    }

    /**
     * @return Activity
     */
    public final function done()
    {
        switch ($this->activityType){
            case 'text':
                $this->activity = new TextActivity($this->biz);
                break;
            default:
                throw new UnexpectedValueException('activity not null');
                break;
        }

        return $this->activity;
    }
}