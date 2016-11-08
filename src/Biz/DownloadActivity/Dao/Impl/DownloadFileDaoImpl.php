<?php
/**
 * User: Edusoho V8
 * Date: 04/11/2016
 * Time: 11:04
 */

namespace Biz\DownloadActivity\Dao\Impl;


use Biz\DownloadActivity\Dao\DownloadFileDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DownloadFileDaoImpl extends GeneralDaoImpl implements DownloadFileDao
{

    protected $table = 'download_file';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime')
        );
    }

    public function findByDownloadActivityId($downloadActivityId)
    {
        $sql = "SELECT * FROM `download_file` WHERE `downloadActivityId` = ?";
        return $this->db()->fetchAll($sql, array($downloadActivityId));
    }

}