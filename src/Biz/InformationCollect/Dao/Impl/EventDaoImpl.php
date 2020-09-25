<?php

namespace Biz\InformationCollect\Dao\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\InformationCollect\Dao\EventDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class EventDaoImpl extends AdvancedDaoImpl implements EventDao
{
    protected $table = 'information_collect_event';

    public function getByActionAndLocation($action, array $location)
    {
        if (!ArrayToolkit::requireds($location, ['targetType', 'targetId'], true)) {
            return null;
        }

        $sql = "
            SELECT {$this->table}.*
            FROM {$this->table}
                INNER JOIN information_collect_location ON {$this->table}.id = information_collect_location.eventId
            WHERE {$this->table}.status = 'open'
                AND {$this->table}.action = ?
                AND information_collect_location.targetType = ?
                AND (information_collect_location.targetId = 0
                    OR information_collect_location.targetId = ?)
            ORDER BY targetId DESC
            LIMIT 1;
        ";

        return $this->db()->fetchAssoc($sql, [$action, $location['targetType'], $location['targetId']]) ?: [];
    }

    public function declares()
    {
        return [
            'serializes' => [
            ],
            'orderbys' => [
                'id', 'createdTime',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
            'conditions' => [
                'id = :id',
                'title like :title',
                'createdTime >= :startDate',
                'createdTime < :endDate',
            ],
        ];
    }
}
