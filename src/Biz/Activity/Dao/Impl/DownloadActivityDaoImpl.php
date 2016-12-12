<?php
/**
 * User: Edusoho V8
 * Date: 03/11/2016
 * Time: 15:41
 */

namespace Biz\DownloadActivity\Dao\Impl;


use Biz\DownloadActivity\Dao\DownloadActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DownloadActivityDaoImpl extends GeneralDaoImpl implements DownloadActivityDao
{
    protected $table = 'download_activity';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array('media' => 'json', 'linkMedias' => 'json', 'fileMediaIds' => 'json'),
        );
    }


}