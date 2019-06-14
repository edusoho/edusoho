<?php

namespace Biz\Notification\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Notification\Dao\NotificationBatchDao;
use Biz\Notification\Dao\NotificationEventDao;
use Biz\Notification\Dao\NotificationStrategyDao;
use Biz\Notification\Service\NotificationService;
use AppBundle\Common\Exception\RuntimeException;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use AppBundle\Component\Notification\WeChatTemplateMessage\TemplateUtil;

class NotificationServiceImpl extends BaseService implements NotificationService
{
    public function searchBatches($conditions, $orderbys, $start, $limit, $columns = array())
    {
        return $this->getNotificationBatchDao()->search($conditions, $orderbys, $start, $limit, $columns);
    }

    public function countBatches($conditions)
    {
        return $this->getNotificationBatchDao()->count($conditions);
    }

    public function getBatch($id)
    {
        return $this->getNotificationBatchDao()->get($id);
    }

    public function createBatch($batch)
    {
        if (!ArrayToolkit::requireds($batch, array('eventId', 'sn', 'strategyId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $batch = ArrayToolkit::parts($batch, array('eventId', 'sn', 'extra', 'strategyId'));

        return $this->getNotificationBatchDao()->create($batch);
    }

    public function getEvent($id)
    {
        return $this->getNotificationEventDao()->get($id);
    }

    public function findEventsByIds($ids)
    {
        return $this->getNotificationEventDao()->findByEventIds($ids);
    }

    public function createEvent($event)
    {
        if (!ArrayToolkit::requireds($event, array('title', 'content', 'totalCount', 'status'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $event = ArrayToolkit::parts($event, array('title', 'content', 'totalCount', 'status'));

        return $this->getNotificationEventDao()->create($event);
    }

    public function createStrategy($strategy)
    {
        if (!ArrayToolkit::requireds($strategy, array('eventId', 'type', 'seq'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $strategy = ArrayToolkit::parts($strategy, array('eventId', 'type', 'seq'));

        return $this->getNotificationStrategyDao()->create($strategy);
    }

    public function batchHandleNotificationResults($batchs)
    {
        if (empty($batchs)) {
            return array();
        }

        $batchs = array_filter($batchs, function ($item) {
            if ('finished' != $item['status']) {
                return true;
            }
        });
        $sns = ArrayToolkit::column($batchs, 'sn');
        try {
            $result = $this->getSDKNotificationService()->batchGetNotifications($sns);
        } catch (\Exception $e) {
            throw new RuntimeException('获取发送结果错误');
        }

        if (empty($result['data'])) {
            return array();
        }

        $cloudRecords = ArrayToolkit::index($result['data'], 'sn');
        $this->batchUpdateRecord($batchs, $cloudRecords);
    }

    public function createWeChatNotificationRecord($sn, $key, $data)
    {
        $templates = TemplateUtil::templates();
        $template = $templates[$key];
        $content = $this->spliceContent($template['detail'], $data);
        $event = array(
            'title' => $template['name'],
            'content' => $content,
            'totalCount' => 0,
            'status' => 'sending',
        );
        $event = $this->createEvent($event);
        $strategy = array(
            'eventId' => $event['id'],
            'type' => 'wechat',
            'seq' => 1,
        );
        $strategy = $this->createStrategy($strategy);
        $batch = array(
            'eventId' => $event['id'],
            'strategyId' => $strategy['id'],
            'sn' => $sn,
            'status' => 'created',
        );

        return $this->createBatch($batch);
    }

    protected function spliceContent($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{'.$key.'.DATA}}', $value['value'], $content);
        }

        return $content;
    }

    protected function batchUpdateRecord($batchs, $results)
    {
        $eventHelper = new BatchUpdateHelper($this->getNotificationEventDao());
        $batchHelper = new BatchUpdateHelper($this->getNotificationBatchDao());
        foreach ($batchs as $batch) {
            if (!empty($results[$batch['sn']]) && $results[$batch['sn']]['is_finished']) {
                $eventHelper->add('id', $batch['eventId'], array(
                    'totalCount' => $results[$batch['sn']]['total_count'],
                    'succeedCount' => $results[$batch['sn']]['succeed_count'],
                    'status' => 'finish',
                    'reason' => empty($results[$batch['sn']]['failure_reason']) ? array() : $results[$batch['sn']]['failure_reason'],
                ));
                $batchHelper->add('id', $batch['id'], array('status' => 'finished'));
            }
        }
        $eventHelper->flush();
        $batchHelper->flush();
    }

    protected function getSDKNotificationService()
    {
        return $this->biz['qiQiuYunSdk.notification'];
    }

    /**
     * @return NotificationBatchDao
     */
    protected function getNotificationBatchDao()
    {
        return $this->createDao('Notification:NotificationBatchDao');
    }

    /**
     * @return NotificationEventDao
     */
    protected function getNotificationEventDao()
    {
        return $this->createDao('Notification:NotificationEventDao');
    }

    /**
     * @return NotificationStrategyDao
     */
    protected function getNotificationStrategyDao()
    {
        return $this->createDao('Notification:NotificationStrategyDao');
    }
}
