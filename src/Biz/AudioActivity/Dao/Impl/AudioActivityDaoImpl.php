<?php
/**
 * User: Edusoho V8
 * Date: 02/11/2016
 * Time: 15:41
 */

namespace Biz\AudioActivity\Dao\Impl;


use Biz\AudioActivity\Dao\AudioActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AudioActivityDaoImpl extends GeneralDaoImpl implements AudioActivityDao
{
    protected $table = 'audio_activity';

    public function declares()
    {
        return array(
            'serializes' => array('media' => 'json'),
        );
    }
}