<?php


namespace Biz\WeChatNotification\Dao;


use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface WeChatSubscribeRecordDao extends AdvancedDaoInterface
{
    public function getLastRecord();
}