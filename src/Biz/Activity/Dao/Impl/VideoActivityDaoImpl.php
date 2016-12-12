<?php
/**
 * User: Edusoho V8
 * Date: 01/11/2016
 * Time: 14:17
 */

namespace Biz\Activity\Type\Video\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\VideoActivity\Dao\VideoActivityDao;

class VideoActivityDaoImpl extends GeneralDaoImpl implements VideoActivityDao
{
    protected $table = 'video_activity';

    public function declares()
    {
        return array(
            'serializes' => array('media' => 'json'),
        );
    }
}