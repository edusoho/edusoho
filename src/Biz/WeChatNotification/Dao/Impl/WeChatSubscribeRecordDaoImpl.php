<?php


namespace Biz\WeChatNotification\Dao\Impl;


use Biz\WeChatNotification\Dao\WeChatSubscribeRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WeChatSubscribeRecordDaoImpl extends AdvancedDaoImpl implements WeChatSubscribeRecordDao
{

    protected $table = 'wechat_subscribe_record';

    public function declares()
    {
        return [
            'orderbys' => ['createdTime'],
            'conditions' => [
                'id = :id',
                'templateCode = :templateCode',
                'templateType = :templateType',
                'createdTime' < 'createdTime_LT'
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ]
        ];
    }

    public function getLastRecord()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql) ?: null;
    }
}