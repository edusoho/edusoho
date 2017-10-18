<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    private $pageSize = 1000;

    protected $systemUserId = 0;

    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $systemUser = $this->getConnection()->fetchAssoc("select * from user where type='system';");
            $this->systemUserId = $systemUser['id'];

            $result = $this->updateScheme($index);

            $this->getConnection()->commit();

            if (!empty($result)) {
                return $result;
            } else {
                $this->logger('info', '执行升级脚本结束');
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            $this->logger('error', $e->getTraceAsString());
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . "/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger( 'info', '删除缓存');
        return 1;
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'createTables',    // done
            'migrateBizOrders', // done
            'updateBizOrders',
            'migrateBizOrderItems', // done
            'updateBizOrderItems',
            'migrateBizOrderItemDeductsByCoupon', // done
            'migrateBizOrderItemDeductsByDiscount', // done
            'migrateBizOrderRefund', // done
            'migrateBizOrderRefundItems', // done
            'migrateBizOrderLog',
            'migrateBizPaymentTrade', // done
            'migrateBizPaymentTradeFromCashOrder', // done
            'updateBizPaymentTradePlatforms',
            'migrateBizSecurityAnswer', // done
            'migrateBizPayAccount',   // done
            'migrateBizUserCashflowAsUser',
            'migrateBizUserCashflowAsSiteByCoin',
            'migrateBizUserCashflowAsSiteByMoney',
            'migrateBizUserCashflowPlatform',
            'migrateBizUserBalance',  // done
            'registerJobs', // done
            'migrateJoinMemberOperationRecord',
            'migrateExitMemberOperationRecord',
            'stopCrmJobs',
            'updateCourseIsFree',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key+1] = $funcName;
        }


        if ($index == 0) {
            $this->logger( 'info', '开始执行升级脚本');
            $this->deleteCache();

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if ($page == 1) {
            $step++;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0
            );
        }
    }

    protected function migrateBizOrders($page)
    {
        $this->addMigrateId('biz_order');

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) from orders WHERE id NOT IN (SELECT migrate_id FROM `biz_order`)");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            insert into `biz_order` (
                `id`,
                `title`,
                `sn`,
                `source`,
                `created_reason`,
                `price_amount`,
                `price_type`,
                `pay_amount`,
                `user_id`,
                `callback`,
                `trade_sn`,
                `status`,
                `pay_time`,
                `payment`,
                `finish_time`,
                `close_time`,
                `close_data`,
                `close_user_id`,
                `seller_id`,
                `created_user_id`,
                `create_extra`,
                `device`,
                `paid_cash_amount`,
                `paid_coin_amount`,
                `refund_deadline`,
                `created_time`,
                `updated_time`,
                `migrate_id`
            )
            select 
                `id`,
                `title`,
                `sn`,
                'self' as `source`,
                '' as `created_reason`,
                round(`totalPrice`*100) as `price_amount`,
                `priceType` as `price_type`,
                round((`amount` + `coinAmount`/coinRate)*100) as `pay_amount`,
                `userId` as `user_id`,
                '' as `callback`,
                `sn` as `trade_sn`, -- trade_sn和sn一致
                case when `status` in ('paid', 'refunding') then 'success' when `status` = 'cancelled' then 'closed' else `status` end as `status`,
                `paidTime` as `pay_time`,
                case when `payment` in ('alipay', 'coin', 'heepay', 'llpay', 'none', 'quickpay', 'wxpay') then `payment` else 'none' end as `payment`,
                `paidTime` as `finish_time`,
                case when `status` = 'cancelled' then `updatedTime` else 0 end as `close_time`, -- TODO 当订单关闭状态时的时间, 从日志中取得
                '' as `close_data`, -- TODO 当订单关闭状态时的数据, 从日志中取得
                case when `status` = 'cancelled' then {$this->systemUserId} else 0 end as `close_user_id`, -- TODO 当订单关闭状态时的操作人, 从日志中取得
                0 as `seller_id`,
                `userId` as `created_user_id`, -- TODO 创建订单者
                '' as `create_extra`, -- 不迁移data数据
                '' as `device`, -- TODO 处理device字段, 下单设备：app, pc, 手机
                round(`amount`*100) as `paid_cash_amount`,
                round(`coinAmount`*100) as `paid_coin_amount`,
                `refundEndTime` as `refund_deadline`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `id` as `migrate_id`
            from orders where id not in (select migrate_id from `biz_order`) LIMIT 0, {$this->pageSize};
        ");

        $this->logger('info', "处理biz_orders数据，当前页码{$page}");

        return $page + 1;
    }

    protected function updateBizOrders($page)
    {
        $connection = $this->getConnection();
        $connection->exec("update biz_order set source = 'marketing' where migrate_id in (select id from orders where payment='marketing');");
        $connection->exec("update biz_order set source = 'outside' where migrate_id in (select id from orders where payment='outside');");

        $connection->exec("update biz_order set payment = 'lianlianpay' where payment = 'llpay';");
        $connection->exec("update biz_order set payment = 'wechat' where payment = 'wxpay';");

        $this->logger('info', "更新处理biz_orders数据，当前页码{$page}");

        return 1;
    }

    protected function migrateBizOrderItems($page)
    {
        $this->addMigrateId('biz_order_item');

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) FROM orders WHERE id NOT in (select migrate_id from `biz_order_item`)");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            insert into `biz_order_item` (
                `id`,
                `order_id`,
                `sn`,
                `title`,
                `detail`,
                `num`,
                `unit`,
                `status`,
                `refund_id`,
                `refund_status`,
                `price_amount`,
                `pay_amount`,
                `target_id`,
                `target_type`,
                `pay_time`,
                `finish_time`,
                `close_time`,
                `user_id`,
                `seller_id`,
                `create_extra`,
                `snapshot`,
                `created_time`,
                `updated_time`,
                `migrate_id`
            ) 
            select 
                `o`.`id`,
                `o`.`id` as `order_id`,
                `o`.`sn` as `sn`,
                `o`.`title` as `title`,
                '' as `detail`,
                1 as `num`,    -- 处理会员的数据
                '' as `unit`,  -- 处理会员的数据
                case when `o`.`status` in ('paid', 'refunding') then 'success' when `o`.`status` = 'cancelled' then 'closed' else `o`.`status` end  as `status`, -- TODO 保持和biz_order一样
                `o`.`refundId` as `refund_id`,
                case when re.status = 'refunded' then 'refunded' else '' end as `refund_status`, -- 当有refund_id时，冗余的退款状态
                round(`o`.`totalPrice`*100) as `price_amount`,
                case when (o.`totalPrice`*100 - o.`couponDiscount`*100 - o.`discount`*100) < 0 then 0 else round(o.`totalPrice`*100 - o.`couponDiscount`*100 - o.`discount`*100) end as `pay_amount`, -- 应付款
                `o`.`targetId` as `target_id`,
                `o`.`targetType` as `target_type`,
                `o`.`paidTime` as `pay_time`,
                `o`.`paidTime` as `finish_time`,
                case when o.`status` = 'cancelled' then o.`updatedTime` else 0 end as `close_time`, -- TODO 当订单关闭状态时的时间, 从日志中取得
                `o`.`userId` as `user_id`,
                0 as `seller_id`,
                '' as `create_extra`,
                '' as `snapshot`,
                `o`.`createdTime` as `created_time`,
                `o`.`updatedTime` as `updated_time`,
                `o`.`id` as `migrate_id`
            from orders o left join order_refund re on o.refundId = re.id where `o`.`id` not in (select migrate_id from `biz_order_item`) LIMIT 0, {$this->pageSize};
        ");

        $this->logger('info', "处理biz_order_item数据，当前页码{$page}");

        return $page + 1;
    }

    // TODO 处理时间太长
    protected function updateBizOrderItems($page)
    {
        $connection = $this->getConnection();

        $count = $connection->fetchColumn("select count(*) from orders where targetType='vip' and id not in (select migrate_id from `biz_order_item` where unit<>'' and target_type='vip');");

        if (empty($count)) {
            return 1;
        }

        // 处理会员订单
        $vipOrders = $connection->fetchAll("select `id`, `data` from orders where targetType='vip' and id not in (select migrate_id from `biz_order_item` where unit<>'' and target_type='vip') LIMIT 0, {$this->pageSize};");

        foreach ($vipOrders as $vipOrder) {
            $data = json_decode($vipOrder['data'], true);
            $buyType = empty($data['buyType']) ? 'new' : $data['buyType'];
            $buyType = json_encode(array('buyType' => $buyType));
            $duration = empty($data['duration']) ? 0 : $data['duration'];
            $unit = empty($data['unitType']) ? 0 : $data['unitType'];
            if ($duration<0) {
                $duration = abs($duration);
            }
            $connection->exec("update biz_order_item set create_extra = '{$buyType}', num = '{$duration}', unit = '{$unit}' where migrate_id = {$vipOrder['id']} and target_type = 'vip';");
        }

        $this->logger('info', "更新处理biz_order_item数据，当前页码{$page}");

        return $page + 1;
    }

    // TODO 处理时间过长
    protected function migrateBizOrderItemDeductsByCoupon($page)
    {
        $this->addMigrateId('biz_order_item_deduct');

        $connection = $this->getConnection();

        // 有可能copon批次会删除，导致老数据不会迁移
        $count = $connection->fetchColumn("SELECT COUNT(o.id) FROM orders o left join coupon c on o.coupon = c.code where o.coupon is not null and c.id is not null and o.id NOT IN (SELECT migrate_id FROM `biz_order_item_deduct` WHERE `deduct_type` = 'coupon');");

        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_order_item_deduct` (
                `order_id`,
                `detail`,
                `item_id`,
                `deduct_type`,
                `deduct_id`,
                `deduct_amount`,
                `status`,
                `user_id`,
                `seller_id`,
                `snapshot`,
                `created_time`,
                `updated_time`,
                `migrate_id`
            ) 
            select 
                o.`id` as `order_id`,
                '' as `detail`,
                o.`id` as `item_id`,
                'coupon' as `deduct_type`,
                c.`id` as `deduct_id`,               
                round(o.`couponDiscount`*100) as `deduct_amount`,
                case when `o`.`status` in ('paid', 'refunding') then 'success' when `o`.`status` = 'cancelled' then 'closed' else `o`.`status` end  as `status`, -- TODO 保持和biz_order一样
                o.`userId` as `user_id`,
                0 as `seller_id`,
                concat('{\"couponCode\":\'', o.coupon, '\'}') as `snapshot`,
                o.`createdTime` as `created_time`,
                o.`updatedTime` as `updated_time`,
                o.`id` as `migrate_id`
            from orders o inner join coupon c on o.coupon = c.code where o.coupon is not null and c.id is not null and o.id not in (select migrate_id from `biz_order_item_deduct` where `deduct_type` = 'coupon') LIMIT 0, {$this->pageSize};
        ");

        $this->logger('info', "处理biz_order_item_deduct的优惠码数据，当前页码{$page}");

        return $page + 1;
    }

    // TODO 处理时间过长
    protected function migrateBizOrderItemDeductsByDiscount($page)
    {
        $this->addMigrateId('biz_order_item_deduct');

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) FROM orders WHERE discountId > 0 and id NOT IN (SELECT migrate_id FROM `biz_order_item_deduct` WHERE `deduct_type` = 'discount');");

        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_order_item_deduct` (
                `order_id`,
                `detail`,
                `item_id`,
                `deduct_type`,
                `deduct_id`,
                `deduct_amount`,
                `status`,
                `user_id`,
                `seller_id`,
                `snapshot`,
                `created_time`,
                `updated_time`,
                `migrate_id`
            ) 
            select 
                `id` as `order_id`,
                '' as `detail`,
                `id` as `item_id`,
                'discount' as `deduct_type`,
                `discountId` as `deduct_id`,              
                round(`discount`*100) as `deduct_amount`,
                case when `o`.`status` in ('paid', 'refunding') then 'success' when `o`.`status` = 'cancelled' then 'closed' else `o`.`status` end  as `status`, -- TODO 保持和biz_order一样
                `userId` as `user_id`,
                0 as `seller_id`,
                '' as `snapshot`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `id` as `migrate_id`
            from orders o where discountId > 0 and id not in (select migrate_id from `biz_order_item_deduct` where `deduct_type` = 'discount') LIMIT 0, {$this->pageSize};
        ");

        $this->logger('info', "处理biz_order_item_deduct的打折数据，当前页码{$page}");

        return $page + 1;
    }

    protected function migrateBizOrderRefund($page)
    {
        $this->addMigrateId('biz_order_refund');

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) FROM `order_refund` where `id` not in (select migrate_id from `biz_order_refund`)");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_order_refund` (
                `id`,
                `title`,
                `order_id`,
                `order_item_id`,
                `sn`,
                `user_id`,
                `reason`,
                `currency`,
                `deal_time`,
                `deal_user_id`,
                `status`,
                `deal_reason`,
                `created_user_id`,
                `amount`,
                `refund_cash_amount`,
                `refund_coin_amount`,
                `created_time`,
                `updated_time`,
                `migrate_id`
            )
            select 
                `r`.`id`,
                `o`.`title` as `title`,
                `r`.`orderId` as `order_id`,
                `r`.`orderId` as `order_item_id`,
                concat(`r`.`createdTime`, round(RAND() * 10000)) as `sn`,
                `r`.`userId` as `user_id`,
                `r`.`reasonNote` as `reason`,
                'CYN' as `currency`,
                `r`.`updatedTime` as `deal_time`,
                `r`.`operator` as `deal_user_id`,
                case when `r`.`status` = 'cancelled' then 'cancel' when `r`.`status` = 'created' then 'auditing' when `r`.`status` = 'success' then 'refunded' when `r`.`status` = 'failed' then 'refused' else `r`.`status` end as `status`,
                '' as `deal_reason`, -- TODO 该信息是从日志表、消息表中获取
                `r`.`userId` as `created_user_id`,
                round(`r`.`actualAmount`*100) as `amount`,
                round(`r`.`actualAmount`*100) as `refund_cash_amount`,
                0 as `refund_coin_amount`,
                `r`.`createdTime` as `created_time`,
                `r`.`updatedTime` as `updated_time`,
                `r`.`id` as `migrate_id`
            from `order_refund` `r` left join `orders` `o` on `r`.`orderId`=`o`.`id` where r.`id` not in (select migrate_id from `biz_order_refund`) LIMIT 0, {$this->pageSize}
        ");

        $this->logger('info', "处理biz_order_refund的数据，当前页码{$page}");

        return $page + 1;
    }

    protected function migrateBizOrderRefundItems($page)
    {
        $this->addMigrateId('biz_order_item_refund');

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) FROM `order_refund` where `id` not in (select migrate_id from `biz_order_item_refund`)");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_order_item_refund` (
                `id`,
                `order_refund_id`,
                `order_id`,
                `order_item_id`,
                `user_id`,
                `amount`,
                `coin_amount`,
                `status`,
                `created_user_id`,
                `created_time`,
                `updated_time`,
                `target_id`,
                `target_type`,
                `migrate_id`
            )
            select 
                `id`,
                `id` as `order_refund_id`,
                `orderId` as `order_id`,
                `orderId` as `order_item_id`,
                `userId` as `user_id`,
                case when `expectedAmount` is null then 0 else `expectedAmount` end as `amount`,
                0 as `coin_amount`,
                `status` as `status`,
                `userId` as `created_user_id`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `targetId` as `target_id`,
                `targetType` as `target_type`,
                `id` as `migrate_id`
            from `order_refund` where `id` not in (select migrate_id from `biz_order_item_refund`) LIMIT 0, {$this->pageSize}
        ");

        $this->logger('info', "处理biz_order_item_refund的数据，当前页码{$page}");

        return $page + 1;
    }

    protected function migrateBizOrderLog($page)
    {
        // TODO
        return 1;
    }

    protected function migrateBizPaymentTrade($page)
    {
        $this->addMigrateId('biz_payment_trade');

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) from `orders` where `id` not in (select migrate_id from `biz_payment_trade` where `type` = 'purchase')");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_payment_trade` (
                `id`,
                `title`,
                `trade_sn`,
                `order_sn`,
                `platform`,
                `platform_sn`,
                `status`,
                `price_type`,
                `currency`,
                `amount`,
                `coin_amount`,
                `cash_amount`,
                `rate`,
                `type`,
                `pay_time`,
                `seller_id`,
                `user_id`,
                `notify_data`,
                `platform_created_result`,
                `apply_refund_time`,
                `refund_success_time`,
                `platform_created_params`,
                `platform_type`,
                `updated_time`,
                `created_time`,
                `migrate_id`
            )
            select 
                o.`id`,
                o.`title`,
                o.`sn` as `trade_sn`,
                o.`sn` as `order_sn`,
                o.`payment` as `platform`,
                '' as `platform_sn`,
                o.`status` as `status`,
                'money' as `price_type`,
                'CNY' as `currency`,
                round(o.`amount`*100),
                round(o.`coinAmount`*100) as `coin_amount`,
                round(o.`amount`*100) as `cash_amount`,
                o.`coinRate` as `rate`,
                'purchase' as `type`,
                o.`paidTime` as `pay_time`,
                0 as `seller_id`,
                o.`userId` as `user_id`,
                '' as `notify_data`,
                '' as `platform_created_result`,
                case when r.createdTime is not null then r.`createdTime` else 0 end as `apply_refund_time`, -- TODO join refund
                case when r.status = 'success' and r.updatedTime is not null then r.updatedTime else 0 end as `refund_success_time`, -- TODO join refund
                '' as `platform_created_params`,
                o.payment as `platform_type`, -- TODO
                o.`updatedTime` as `updated_time`,
                o.`createdTime` as `created_time`,
                o.`id` as `migrate_id`
            from `orders` o left join `order_refund` r on o.refundId=r.id where o.`id` not in (select migrate_id from `biz_payment_trade` where `type` = 'purchase') LIMIT 0, {$this->pageSize}
        ");

        $this->logger('info', "处理biz_payment_trade的数据，当前页码{$page}");

        return $page + 1;
    }

    protected function migrateBizPaymentTradeFromCashOrder($page)
    {
        $this->addMigrateId('biz_payment_trade');

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) from `cash_orders` where `id` not in (select migrate_id from `biz_payment_trade` where `type` = 'recharge')");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_payment_trade` (
                `title`,
                `trade_sn`,
                `order_sn`,
                `platform`,
                `platform_sn`,
                `status`,
                `price_type`,
                `currency`,
                `amount`,
                `coin_amount`,
                `cash_amount`,
                `rate`,
                `type`,
                `pay_time`,
                `seller_id`,
                `user_id`,
                `notify_data`,
                `platform_created_result`,
                `apply_refund_time`,
                `refund_success_time`,
                `platform_created_params`,
                `platform_type`,
                `updated_time`,
                `created_time`,
                `migrate_id`
            )
            select 
                `title`,
                `sn` as `trade_sn`,
                '' as `order_sn`,
                `payment` as `platform`,
                '' as `platform_sn`,
                `status` as `status`,
                'money' as `price_type`,
                'CNY' as `currency`,
                round(`amount`*100),
                '0' as `coin_amount`,
                round(`amount`*100) as `cash_amount`,
                '1' as `rate`, -- TODO 当前系统汇率
                'recharge' as `type`,
                `paidTime` as `pay_time`,
                0 as `seller_id`,
                `userId` as `user_id`,
                '' as `notify_data`,
                '' as `platform_created_result`,
                0 as `apply_refund_time`,
                0 as `refund_success_time`,
                '' as `platform_created_params`,
                '' as `platform_type`,
                `paidTime` as `updated_time`,
                `createdTime` as `created_time`,
                `id` as `migrate_id`
            from `cash_orders` where `id` not in (select migrate_id from `biz_payment_trade` where `type` = 'recharge') LIMIT 0, {$this->pageSize}
        ");

        $this->logger('info', "处理biz_payment_trade的现金订单数据，当前页码{$page}");

        return $page + 1;
    }

    protected function migrateBizSecurityAnswer($page)
    {
        $this->addMigrateId('biz_security_answer');

        $connection = $this->getConnection();
        $count = $connection->fetchColumn("SELECT COUNT(id) from user_secure_question where id not in (select migrate_id from biz_security_answer)");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_security_answer` (
                `id`,
                `user_id`,
                `question_key`,
                `answer`,
                `salt`,
                `created_time`,
                `updated_time`,
                `migrate_id`
            )
            select 
                `id`,
                `userId` as `user_id`,
                `securityQuestionCode` as `question_key`,
                `securityAnswer` as `answer`,
                `securityAnswerSalt` as `salt`,
                `createdTime` as `created_time`,
                `createdTime` as `updated_time`,
                `id` as `migrate_id`
            from user_secure_question where id not in (select migrate_id from biz_security_answer) LIMIT 0, {$this->pageSize}
        ");

        $this->logger('info', "处理biz_security_answer的数据，当前页码{$page}");

        return $page + 1;
    }

    protected function migrateBizPayAccount($page)
    {
        $this->addMigrateId('biz_pay_account');

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) from `user` where `id` not in (select `migrate_id` from `biz_pay_account`)");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_pay_account` (
              `id`,
              `user_id`,
              `password`,
              `salt`,
              `created_time`,
              `updated_time`,
              `migrate_id`
            )
            select
              `id`,
              `id`,
              `payPassword`,
              `payPasswordSalt`,
              `createdTime`,
              `updatedTime`,
              `id`
            from `user` u where u.`id` not in (select `migrate_id` from `biz_pay_account`) LIMIT 0, {$this->pageSize}
        ");

        $this->logger('info', "处理biz_pay_account的数据，当前页码{$page}");

        return $page + 1;
    }

    protected function migrateBizUserBalance($page)
    {
        $this->addMigrateId('biz_user_balance');

        $connection = $this->getConnection();
        $count = $connection->fetchColumn("SELECT count(id) FROM `user` where id not in (select `migrate_id` from `biz_user_balance`)");

        if (empty($count)) {
            $sql = "select * from `biz_user_balance` where user_id = 0;";
            $result = $this->getConnection()->fetchAssoc($sql);
            if (empty($result)) {
                $currentTime = time();

                $total = $connection->fetchColumn('select sum(cash) from cash_account');
                $total = 0 - $total*100;

                $connection->exec("insert into `biz_user_balance` (`user_id`, `amount`, `created_time`, `updated_time`) values (0, {$total}, {$currentTime}, {$currentTime});");
            }

            return 1;
        }

        $connection->exec("
            INSERT into `biz_user_balance` (
              `id`,
              `user_id`,
              `amount`,
              `created_time`,
              `updated_time`,
              `migrate_id`
            )
            select
              u.`id`,
              u.`id` as `user_id`,
              case when ca.`cash`*100 is null then 0 else round(ca.`cash`*100) end as `amount`,
              u.`createdTime` as `created_time`,
              u.`updatedTime` as `updated_time`,
              u.`id` as `migrate_id`
            from `user` u left join cash_account ca on u.`id` = ca.`userId`  where u.`id` not in (select `migrate_id` from `biz_user_balance`) LIMIT 0, {$this->pageSize}
        ");

        $this->logger('info', "处理biz_user_balance的数据，当前页码{$page}");

        return $page + 1;
    }

    protected function migrateBizUserCashflowAsUser($page)
    {
        return $this->migrateBizUserCashflow($page, 'user');
    }

    protected function migrateBizUserCashflowAsSiteByCoin($page)
    {
        return $this->migrateBizUserCashflow($page, 'site', 'coin');
    }

    protected function migrateBizUserCashflowAsSiteByMoney($page)
    {
        return $this->migrateBizUserCashflow($page, 'site', 'money');
    }

    protected function migrateBizUserCashflow($page, $userIdType, $amountType = '')
    {
        $this->addMigrateId('biz_user_cashflow');

        $connection = $this->getConnection();

        if ($userIdType == 'user') {
            $migrateUserId = 'uf.`userId` as `user_id`,';
            $migrateType = "uf.`type` as `type`,";
            $migrateSn = "uf.`sn` as `sn`,";

            $whereSql = "uf.amount>0 and uf.`id` not in (select `migrate_id` from `biz_user_cashflow` where user_id<>0) LIMIT 0, {$this->pageSize}";
        }

        if ($userIdType=='site') {
            $migrateUserId = '0 as `user_id`,';
            $migrateType = "case when `type`='inflow' then 'outflow' when `type`='outflow' then 'inflow' else `type` end as `type`,";
            $migrateSn = "concat(uf.`sn`,'0') as `sn`,";

            $whereSql = '';
            if($amountType == 'money') {
                $whereSql = "uf.`type` = 'outflow' and uf.`cashType`='RMB' and ";
            }

            if($amountType == 'coin') {
                $whereSql = "uf.`cashType`='coin' and ";
            }

            $whereSql = "{$whereSql} uf.amount>0 and uf.`id` not in (select `migrate_id` from `biz_user_cashflow` where user_id=0) LIMIT 0, {$this->pageSize}";

        }

        $count = $connection->fetchColumn("SELECT COUNT(id) from `cash_flow` uf where {$whereSql}");
        if (empty($count)) {
            return 1;
        }

        $sql = "
            insert into `biz_user_cashflow` (
                `title`,
                `sn`,
                `parent_sn`,
                `user_id`,
                `buyer_id`,
                `type`,
                `amount`,
                `currency`,
                `user_balance`,
                `order_sn`,
                `trade_sn`,
                `platform`,
                `amount_type`,
                `created_time`,
                `action`,
                `migrate_id`
            )
            select
                case when o.`title` is not null then o.`title` else uf.`name` end as `title`,
                {$migrateSn}
                uf.`parentSn` as `parent_sn`,
                {$migrateUserId}
                uf.`userId` as `buyer_id`,
                {$migrateType}
                round(uf.`amount`*100) as `amount`,
                case when uf.`cashType`='Coin' then 'coin' else 'CNY' end as `currency`,
                uf.`cash` as `user_balance`, -- TODO
                uf.`orderSn` as `order_sn`,
                uf.`orderSn` as `trade_sn`,
                case when uf.`payment` in ('alipay', 'coin', 'heepay', 'llpay', 'none', 'quickpay', 'wxpay') then uf.`payment` else 'none' end as `platform`,
                case when uf.`cashType`='Coin' then 'coin' else 'money' end as `amount_type`,
                uf.`createdTime` as `created_time`,
                case when uf.`category` = 'charge' then 'recharge' else 'purchase' end  as `action`,
                uf.`id` as `migrate_id`
            from `cash_flow` uf left join orders o on uf.orderSn = o.sn where {$whereSql}
        ";

        $connection->exec($sql);

        $this->logger('info', "处理{$userIdType}的biz_user_cashflow的数据，当前页码{$page}");

        return $page + 1;
    }

    protected function updateBizPaymentTradePlatforms($page)
    {
        $connection = $this->getConnection();
        $connection->exec("update biz_payment_trade set platform=(select payment from biz_order where sn=order_sn) where type='purchase';");

        $connection->exec("update biz_payment_trade set platform = 'lianlianpay' where platform = 'llpay' and type='recharge';");
        $connection->exec("update biz_payment_trade set platform = 'wechat' where platform = 'wxpay' and type='recharge';");
        return 1;
    }

    protected function migrateBizUserCashflowPlatform($page)
    {
        $connection = $this->getConnection();
        $sql = "update `biz_user_cashflow` set platform='none' where amount_type='coin';";
        $connection->exec($sql);

        $sql = "update `biz_user_cashflow` uc set uc.platform=(select platform from biz_payment_trade where trade_sn=uc.trade_sn) where amount_type='money' and trade_sn in (select trade_sn from biz_payment_trade where trade_sn=uc.trade_sn);";
        $connection->exec($sql);
        return 1;
    }

    protected function registerJobs($page)
    {
        $this->getConnection()->exec("DELETE FROM `biz_scheduler_job` WHERE name = 'CancelOrderJob';");

        if (!$this->isJobExist('Order_CloseOrdersJob')) {
            $currentTime = time();
            $this->getConnection()->exec("INSERT INTO `biz_scheduler_job` (
                  `name`,
                  `expression`,
                  `class`,
                  `args`,
                  `priority`,
                  `pre_fire_time`,
                  `next_fire_time`,
                  `misfire_threshold`,
                  `misfire_policy`,
                  `enabled`,
                  `creator_id`,
                  `updated_time`,
                  `created_time`
            ) VALUES (
                  'Order_CloseOrdersJob',
                  '20 * * * *',
                  'Codeages\\\\Biz\\\\Framework\\\\Order\\\\Job\\\\CloseOrdersJob',
                  '',
                  '100',
                  '0',
                  '{$currentTime}',
                  '300',
                  'missed',
                  '1',
                  '0',
                  '{$currentTime}',
                  '{$currentTime}'
            )");
        }

        $this->logger('info', '新增CloseOrdersJob');

        return 1;
    }

    protected function migrateJoinMemberOperationRecord($page)
    {
        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) FROM `orders` where status = 'paid' and `id` not in (select `order_id` from `member_operation_record` where `operate_type` = 'join')");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `member_operation_record` (
                `title`,
                `member_id`,
                `member_type`,
                `target_id`,
                `target_type`,
                `operate_type`,
                `operate_time`,
                `operator_id`,
                `data`,
                `user_id`,
                `order_id`,
                `refund_id`,
                `reason`,
                `created_time`
            )
            select 
                `title` as `title`,
                0 as `member_id`,
                'student' as `member_type`,
                `targetId` as `target_id`,
                `targetType` as `target_type`,
                'join' as `operate_type`,
                `createdTime` as `operate_time`,
                0 as `operator_id`,
                '' as `data`,
                `userId` as `user_id`,
                `id` as `order_id`,
                0 as `refund_id`,
                '' as `reason`,
                `createdTime` as `created_time`
            from `orders` where status = 'paid' and `id` not in (select `order_id` from `member_operation_record` where `operate_type` = 'join') LIMIT 0, {$this->pageSize}
        "); 

        $this->logger('info', "处理member_operation_record的加入数据，当前页码{$page}");

        return $page + 1;       
    }

    protected function migrateExitMemberOperationRecord($page)
    {
        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) from `order_refund` where status = 'success' and `orderId` not in (select `order_id` from `member_operation_record` where `operate_type` = 'exit')");

        if (empty($count)) {
            $connection->exec(
                "UPDATE `member_operation_record` as `mor` , `orders` SET `mor`.`title` = `orders`.`title` where `mor`.`order_id` = `orders`.`id` and `operate_type` = 'exit';"  
            );
            return 1;
        }

        $connection->exec("
            INSERT into `member_operation_record` (
                `title`,
                `member_id`,
                `member_type`,
                `target_id`,
                `target_type`,
                `operate_type`,
                `operate_time`,
                `operator_id`,
                `data`,
                `user_id`,
                `order_id`,
                `refund_id`,
                `reason`,
                `created_time`
            )
            select 
                '' as `title`,
                0 as `member_id`,
                'student' as `member_type`,
                `targetId` as `target_id`,
                `targetType` as `target_type`,
                'exit' as `operate_type`,
                `createdTime` as `operate_time`,
                `operator` as `operator_id`,
                '' as `data`,
                `userId` as `user_id`,
                `orderId` as `order_id`,
                `id` as `refund_id`,
                `reasonNote` as `reason`,
                `createdTime` as `created_time`
            from `order_refund` where status = 'success' and `orderId` not in (select `order_id` from `member_operation_record` where `operate_type` = 'exit') LIMIT 0, {$this->pageSize};
        ");

        $this->logger('info', "处理member_operation_record的退出数据，当前页码{$page}");

        return $page + 1;
    }

    protected function stopCrmJobs()
    {
        $connection = $this->getConnection();
        $connection->exec("UPDATE `biz_scheduler_job` SET `enabled`= 0 where `source` = 'CrmPlugin';");
        
        return 1;
    }

    protected function updateCourseIsFree()
    {
        //打折插件，限时免费会将isFree变为1，现有业务不需要设置成免费，这样显示免费会产生订单
        $connection = $this->getConnection();
        $connection->exec("UPDATE `course_v8` as c ,`course_set_v8` as cs SET c.`isFree` = 0 where c.`originPrice` > 0  and c.courseSetId = cs.id and cs.discountId > 0 and cs.discount = 0;");

        return 1;
    }

    protected function createTables()
    {
        $connection = $this->getConnection();

        if (!$this->isTableExist('biz_order')) {
            $connection->exec("
                CREATE TABLE `biz_order` (
                  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                  `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '订单标题',
                  `sn` VARCHAR(64) NOT NULL COMMENT '订单号',
                  `source` VARCHAR(16) NOT NULL DEFAULT 'self' COMMENT '订单来源：网校本身、营销平台、第三方系统',
                  `created_reason` TEXT COMMENT '订单创建原因, 例如：导入，购买等',
                  `price_amount` INT(12) unsigned NOT NULL COMMENT '订单总金额',
                  `price_type` varchar(32) not null  COMMENT '标价类型，现金支付or虚拟币；money, coin',
                  `pay_amount` INT(10) unsigned NOT NULL COMMENT '应付金额',
                  `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
                  `callback` TEXT COMMENT '商品中心的异步回调信息',
                  `trade_sn` VARCHAR(64) COMMENT '支付的交易号',
                  `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '订单状态',
                  `pay_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
                  `payment` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '支付类型',
                  `finish_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易成功时间，交易成功后不得退款',
                  `close_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易关闭时间',
                  `close_data` TEXT COMMENT '交易关闭描述',
                  `close_user_id` INT(10) unsigned DEFAULT '0' COMMENT '关闭交易的用户',
                  `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
                  `created_user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单的创建者',
                  `create_extra` text COMMENT '创建时的自定义字段，json方式存储',
                  `device` varchar(32) COMMENT '下单设备（pc、mobile、app）',
                  `paid_cash_amount` int(10) unsigned NOT NULL DEFAULT '0',
                  `paid_coin_amount` int(10) unsigned NOT NULL DEFAULT '0',
                  `refund_deadline` int(10) unsigned NOT NULL DEFAULT '0',
                  `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  UNIQUE(`sn`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }
        if (!$this->isTableExist('biz_order_item')) {
            $connection->exec("
                CREATE TABLE `biz_order_item` (
                  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                  `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
                  `sn` VARCHAR(64) NOT NULL COMMENT '编号',
                  `title` VARCHAR(1024) NOT NULL COMMENT '商品名称',
                  `detail` TEXT COMMENT '商品描述',
                  `num` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '数量',
                  `unit` varchar(16) COMMENT '单位',
                  `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '商品状态',
                  `refund_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '最新退款id',
                  `refund_status` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '退款状态',
                  `price_amount` INT(10) unsigned NOT NULL COMMENT '商品价格',
                  `pay_amount` INT(10) unsigned NOT NULL COMMENT '商品应付金额',
                  `target_id` INT(10) unsigned NOT NULL COMMENT '商品id',
                  `target_type` VARCHAR(32) NOT NULL COMMENT '商品类型',
                  `pay_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
                  `finish_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易成功时间，交易成功后不得退款',
                  `close_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易关闭时间',
                  `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
                  `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
                  `create_extra` text COMMENT '创建时的自定义字段，json方式存储',
                  `snapshot` text COMMENT '商品快照',
                  `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  UNIQUE(`sn`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('biz_order_item_deduct')) {
            $connection->exec("
                CREATE TABLE `biz_order_item_deduct` (
                  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                  `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
                  `detail` TEXT COMMENT '描述',
                  `item_id` INT(10) unsigned NOT NULL COMMENT '商品id',
                  `deduct_type` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '促销类型',
                  `deduct_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '对应的促销活动id',
                  `deduct_amount` INT(10) unsigned NOT NULL COMMENT '扣除的价格',
                  `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '商品状态',
                  `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
                  `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
                  `snapshot` text COMMENT '促销快照',
                  `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('biz_order_refund')) {
            $connection->exec("
                CREATE TABLE `biz_order_refund` (
                  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                  `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '订单标题',
                  `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
                  `order_item_id` INT(10) unsigned NOT NULL COMMENT '退款商品的id',
                  `sn` VARCHAR(64) NOT NULL COMMENT '退款订单编号',
                  `user_id` INT(10) unsigned NOT NULL COMMENT '退款人',
                  `reason` TEXT COMMENT '退款的理由',
                  `amount` INT(10) unsigned NOT NULL COMMENT '涉及金额',
                  `currency` VARCHAR(32) NOT NULL DEFAULT 'money' COMMENT '货币类型: coin, money',
                  `deal_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理时间',
                  `deal_user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理人',
                  `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '退款状态',
                  `deal_reason` TEXT COMMENT '处理理由',
                  `created_user_id` INT(10) unsigned NOT NULL COMMENT '申请者',
                  `refund_cash_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退款的现金金额',
                  `refund_coin_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退款的虚拟币金额',
                  `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  UNIQUE(`sn`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('biz_order_item_refund')) {
            $connection->exec("
                CREATE TABLE `biz_order_item_refund` (
                  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                  `order_refund_id` INT(10) unsigned NOT NULL COMMENT '退款订单id',
                  `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
                  `order_item_id` INT(10) unsigned NOT NULL COMMENT '退款商品的id',
                  `user_id` INT(10) unsigned NOT NULL COMMENT '退款人',
                  `amount` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '涉及金额',
                  `coin_amount` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '涉及虚拟币金额',
                  `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '退款状态',
                  `created_user_id` INT(10) unsigned NOT NULL COMMENT '申请者',
                  `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('biz_order_log')) {
            $connection->exec("
                CREATE TABLE `biz_order_log` (
                  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                  `order_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '订单id',
                  `status` VARCHAR(32) NOT NULL COMMENT '订单状态',
                  `user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户',
                  `deal_data` TEXT COMMENT '处理数据',
                  `order_refund_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '退款id',
                  `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('biz_user_cashflow')) {
            $connection->exec("
                CREATE TABLE `biz_user_cashflow` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '流水名称',
                  `sn` VARCHAR(64) NOT NULL COMMENT '账目流水号',
                  `parent_sn` VARCHAR(64) COMMENT '本次交易的上一个账单的流水号',
                  `user_id` int(10) unsigned NOT NULL COMMENT '账号ID，即用户ID',
                  `buyer_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '买家',
                  `type` enum('inflow','outflow') NOT NULL COMMENT '流水类型',
                  `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '金额',
                  `currency` VARCHAR(32) NOT NULL COMMENT '支付的货币: coin, CNY...',
                  `user_balance` int(10) NOT NULL DEFAULT '0' COMMENT '账单生成后的对应账户的余额，若amount_type为coin，对应的是虚拟币账户，amount_type为money，对应的是现金庄户余额',
                  `order_sn` varchar(64) NOT NULL COMMENT '订单号',
                  `trade_sn` varchar(64) NOT NULL COMMENT '交易号',
                  `platform` VARCHAR(32) NOT NULL DEFAULT 'none' COMMENT '支付平台：none, alipay, wxpay...',
                  `amount_type` VARCHAR(32) NOT NULL COMMENT 'ammount的类型：coin, money, locked_amount',
                  `created_time` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE(`sn`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='帐目流水';
            ");
        }

        if (!$this->isTableExist('biz_user_balance')) {
            $connection->exec("
                CREATE TABLE `biz_user_balance` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `user_id` int(10) unsigned NOT NULL COMMENT '用户',
                  `amount` int(10) NOT NULL DEFAULT '0' COMMENT '账户余额',
                  `cash_amount` int(10) NOT NULL DEFAULT '0' COMMENT '现金余额',
                  `locked_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '冻结虚拟币金额',
                  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  UNIQUE(`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('biz_payment_trade')) {
            $connection->exec("
                CREATE TABLE `biz_payment_trade` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `title` varchar(1024) NOT NULL COMMENT '标题',
                  `trade_sn` varchar(64) NOT NULL COMMENT '交易号',
                  `order_sn` varchar(64) NOT NULL COMMENT '客户订单号',
                  `platform` varchar(32) NOT NULL DEFAULT '' COMMENT '第三方支付平台',
                  `platform_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '第三方支付平台的交易号',
                  `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '交易状态',
                  `price_type` varchar(32) NOT NULL COMMENT '标价类型，现金支付or虚拟币；money, coin',
                  `currency` varchar(32) NOT NULL DEFAULT '' COMMENT '支付的货币类型',
                  `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单的需支付金额',
                  `coin_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '虚拟币支付金额',
                  `cash_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '现金支付金额',
                  `rate` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '虚拟币和现金的汇率',
                  `type` varchar(32) NOT NULL DEFAULT 'purchase' COMMENT '交易类型：purchase，recharge，refund',
                  `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易时间',
                  `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
                  `user_id` INT(10) unsigned NOT NULL COMMENT '买家id',
                  `notify_data` text,
                  `platform_created_result` text,
                  `apply_refund_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请退款时间',
                  `refund_success_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成功退款时间',
                  `platform_created_params` text COMMENT '在第三方系统创建支付订单时的参数信息',
                  `platform_type` text COMMENT '在第三方系统中的支付方式',
                  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  UNIQUE(`trade_sn`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('biz_pay_account')) {
            $connection->exec("
                CREATE TABLE `biz_pay_account` (
                  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                  `user_id` INT(10) unsigned NOT NULL COMMENT '所属用户',
                  `password` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '密码',
                  `salt` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '',
                  `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  UNIQUE(`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('biz_security_answer')) {
            $connection->exec("
                CREATE TABLE `biz_security_answer` (
                  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                  `user_id` INT(10) unsigned NOT NULL COMMENT '所属用户',
                  `question_key` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '安全问题的key',
                  `answer` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '',
                  `salt` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '',
                  `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  UNIQUE (`user_id`, `question_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('member_operation_record')) {
            $connection->exec("
                CREATE TABLE `member_operation_record` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(1024) NOT NULL DEFAULT '' COMMENT '标题',
                `member_id` int(10) UNSIGNED NOT NULL COMMENT '成员ID',
                `member_type` varchar(32) NOT NULL DEFAULT 'student' COMMENT '成员身份',
                `target_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '类型ID',
                `target_type` varchar(32) NOT NULL DEFAULT '' COMMENT '类型（classroom, course）',
                `operate_type` varchar(32) NOT NULL DEFAULT '' COMMENT '操作类型（join, exit）',
                `operate_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作时间',
                `operator_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作用户ID',
                `data` text COMMENT 'extra data',
                `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户Id',
                `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
                `refund_id` int(11) NOT NULL DEFAULT '0' COMMENT '退款ID',
                `reason` varchar(256) NOT NULL DEFAULT '' COMMENT '加入理由或退出理由',
                `created_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;        
            ");
        }

        if (!$this->isFieldExist('biz_order', 'expired_refund_days')) {
            $connection->exec("ALTER TABLE `biz_order` ADD COLUMN `expired_refund_days` int(10) unsigned DEFAULT '0' COMMENT '退款的到期天数'");
        }

        if (!$this->isFieldExist('biz_order', 'success_data')) {
            $connection->exec("ALTER TABLE `biz_order` ADD COLUMN `success_data` text COMMENT '当订单改变为success时的数据记录';");
        }

        if (!$this->isFieldExist('biz_order', 'fail_data')) {
            $connection->exec("ALTER TABLE `biz_order` ADD COLUMN `fail_data` text COMMENT '当订单改变为fail时的数据记录'");
        }

        if (!$this->isFieldExist('biz_order_item_refund', 'target_id')) {
            $connection->exec("ALTER TABLE `biz_order_item_refund` ADD COLUMN `target_id` INT(10) unsigned NOT NULL COMMENT '商品id'");
        }

        if (!$this->isFieldExist('biz_order_item_refund', 'target_type')) {
            $connection->exec("ALTER TABLE `biz_order_item_refund` ADD COLUMN `target_type` VARCHAR(32) NOT NULL COMMENT '商品类型'");
        }

        if ($this->isFieldExist('biz_order', 'price_amount')) {
            $connection->exec("ALTER TABLE `biz_order` MODIFY COLUMN `price_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '订单总价';");
        }

        if ($this->isFieldExist('biz_order', 'pay_amount')) {
            $connection->exec("ALTER TABLE `biz_order` MODIFY COLUMN `pay_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '应付价格';");
        }

        if ($this->isFieldExist('biz_order', 'paid_cash_amount')) {
            $connection->exec("ALTER TABLE `biz_order` MODIFY COLUMN `paid_cash_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '支付的现金价格';");
        }

        if ($this->isFieldExist('biz_order', 'paid_coin_amount')) {
            $connection->exec("ALTER TABLE `biz_order` MODIFY COLUMN `paid_coin_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '支付的虚拟币价格';");
        }

        if ($this->isFieldExist('biz_order_item', 'price_amount')) {
            $connection->exec("ALTER TABLE `biz_order_item` MODIFY COLUMN `price_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '订单价格';");
        }

        if ($this->isFieldExist('biz_order_item', 'pay_amount')) {
            $connection->exec("ALTER TABLE `biz_order_item` MODIFY COLUMN `pay_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '支付价格';");
        }

        if ($this->isFieldExist('biz_order_item_deduct', 'deduct_amount')) {
            $connection->exec("ALTER TABLE `biz_order_item_deduct` MODIFY COLUMN `deduct_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '优惠价格';");
        }

        if ($this->isFieldExist('biz_order_item_refund', 'amount')) {
            $connection->exec("ALTER TABLE `biz_order_item_refund` MODIFY COLUMN `amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '退款现金价格';");
        }

        if ($this->isFieldExist('biz_order_item_refund', 'coin_amount')) {
            $connection->exec("ALTER TABLE `biz_order_item_refund` MODIFY COLUMN `coin_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '退款的虚拟币价格';");
        }

        if ($this->isFieldExist('biz_order_refund', 'amount')) {
            $connection->exec("ALTER TABLE `biz_order_refund` MODIFY COLUMN `amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '退款总价格';");
        }

        if ($this->isFieldExist('biz_order_refund', 'refund_cash_amount')) {
            $connection->exec("ALTER TABLE `biz_order_refund` MODIFY COLUMN `refund_cash_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '退款的现金价格';");
        }

        if ($this->isFieldExist('biz_order_refund', 'refund_coin_amount')) {
            $connection->exec("ALTER TABLE `biz_order_refund` MODIFY COLUMN `refund_coin_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '退款的虚拟币';");
        }

        if ($this->isFieldExist('biz_payment_trade', 'amount')) {
            $connection->exec("ALTER TABLE `biz_payment_trade` MODIFY COLUMN `amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '支付价格';");
        }

        if ($this->isFieldExist('biz_payment_trade', 'coin_amount')) {
            $connection->exec("ALTER TABLE `biz_payment_trade` MODIFY COLUMN `coin_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '虚拟币的支付价格';");
        }

        if ($this->isFieldExist('biz_payment_trade', 'cash_amount')) {
            $connection->exec("ALTER TABLE `biz_payment_trade` MODIFY COLUMN `cash_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '现金的支付价格';");
        }

        if ($this->isFieldExist('biz_user_balance', 'cash_amount')) {
            $connection->exec("ALTER TABLE `biz_user_balance` MODIFY COLUMN `cash_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '现金余额';");
        }

        if ($this->isFieldExist('biz_user_balance', 'amount')) {
            $connection->exec("ALTER TABLE `biz_user_balance` MODIFY COLUMN `amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '虚拟币余额';");
        }

        if ($this->isFieldExist('biz_user_balance', 'locked_amount')) {
            $connection->exec("ALTER TABLE `biz_user_balance` MODIFY COLUMN `locked_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '冻结的虚拟币';");
        }

        if ($this->isFieldExist('biz_user_cashflow', 'amount')) {
            $connection->exec("ALTER TABLE `biz_user_cashflow` MODIFY COLUMN `amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '账单金额';");
        }

        if ($this->isFieldExist('biz_user_cashflow', 'user_balance')) {
            $connection->exec("ALTER TABLE `biz_user_cashflow` MODIFY COLUMN `user_balance` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '生成账单后的用户余额';");
        }

        if (!$this->isFieldExist('biz_user_cashflow', 'action')) {
            $connection->exec("ALTER TABLE `biz_user_cashflow` ADD COLUMN `action` VARCHAR(32) not null default '' COMMENT 'refund, purchase, recharge'");
        }

        if (!$this->isFieldExist('biz_user_balance', 'recharge_amount')) {
            $connection->exec("ALTER TABLE `biz_user_balance` ADD COLUMN `recharge_amount` int(10) NOT NULL DEFAULT '0' COMMENT '充值总额'");
        }

        if (!$this->isFieldExist('biz_user_balance', 'purchase_amount')) {
            $connection->exec("ALTER TABLE `biz_user_balance` ADD COLUMN `purchase_amount` int(10) NOT NULL DEFAULT '0' COMMENT '消费总额'");
        }

        $this->logger('info', '新建biz表');

        return 1;
    }

    protected function addMigrateId($table)
    {
        $connection = $this->getConnection();
        if (!$this->isFieldExist($table, 'migrate_id')) {
            $connection->exec("ALTER TABLE `{$table}` ADD COLUMN `migrate_id` int(10) NOT NULL DEFAULT '0' COMMENT '数据迁移原表id';");
        }
    }

    protected function generateIndex($step, $page)
    {
        return $step * 1000000 + $page;
    }

    protected function getStepAndPage($index)
    {
        $step = intval($index / 1000000);
        $page = $index % 1000000;
        return array($step, $page);
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    protected function getFileUsedDao()
    {
        return $this->createDao('File:FileUsedDao');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Dao\JobDao
     */
    protected function getJobDao()
    {
        return $this->createDao('Scheduler:JobDao');
    }

    /**
     * @return \Biz\CloudPlatform\Service\AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }
}

abstract class AbstractUpdater
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getConnection()
    {
        return $this->biz['db'];
    }

    protected function createService($name)
    {
        return $this->biz->service($name);
    }

    protected function createDao($name)
    {
        return $this->biz->dao($name);
    }

    abstract public function update();

    protected function logger($level, $message)
    {
        $version = \AppBundle\System::VERSION;
        $data = date('Y-m-d H:i:s') . " [{$level}] {$version} " . $message . PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'] . '/../app/logs/upgrade.log';
    }
}
