<?php

namespace Biz\InformationCollect\Dao\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\InformationCollect\Dao\EventDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class EventDaoImpl extends GeneralDaoImpl implements EventDao
{
    protected $table = 'infomation_collect_event';

    public function getEventByActionAndLocation($action, array $location)
    {
        if (!ArrayToolkit::requireds($location, ['targetType', 'targetId'], true)) {
            return (object) [];
        }

        $sql = "
            SELECT {$this->table}.*
            FROM {$this->table}
                INNER JOIN infomation_collect_location ON {$this->table}.id = infomation_collect_location.eventId
            WHERE {$this->table}.status = 'open'
                AND infomation_collect_location.action = ?
                AND infomation_collect_location.targetType = ?
                AND (infomation_collect_location.targetId = 0
                    OR infomation_collect_location.targetId = ?)
            ORDER BY targetId DESC
            LIMIT 1;
        ";

        return $this->db()->fetchAssoc($sql, [$action, $location['targetType'], $location['targetId']]) ?: (object) [];
    }

    public function declares()
    {
        return [
            'serializes' => [
            ],
            'orderbys' => [
                'id',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
