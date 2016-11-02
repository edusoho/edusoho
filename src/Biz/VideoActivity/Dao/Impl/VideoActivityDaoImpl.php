<?php
/**
 * User: Edusoho V8
 * Date: 01/11/2016
 * Time: 14:17
 */

namespace Biz\VideoActivity\Dao\Impl;


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

    public function updateByActivityId($activityId, $fields)
    {
        $this->db()->update($this->table, $fields, array('activityId' => $activityId));

        return $this->getByActivityId($activityId);
    }

    public function getByActivityId($activityId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE activityId = ?";
        return $this->db()->fetchAssoc($sql, array($activityId)) ?: null;
    }
}