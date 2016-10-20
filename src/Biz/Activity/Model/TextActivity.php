<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 11:13
 */

namespace Biz\Activity\Model;


use Biz\Activity\Config\TextActivityConfig;
use Biz\Activity\Event\TextFinishEvent;
use Biz\Activity\Service\ActivityService;

class TextActivity extends Activity
{
    public $name = '图文';

    public function getConfig()
    {
        return new TextActivityConfig($this->getBiz());
    }



    public function create($fields)
    {
        parent::create($fields);
    }


}