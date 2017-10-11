<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
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
        $funcNames = array(
            1 => 'createTables',    // done
            2 => 'migrateBizOrders', // done
            3 => 'migrateBizOrderItems', // done
            4 => 'migrateBizOrderItemDeductsByCoupon', // done
            5 => 'migrateBizOrderItemDeductsByDiscount', // done
            6 => 'migrateBizOrderItemDeductsStatus', // done
            7 => 'migrateBizOrderRefund', // done
            8 => 'migrateBizOrderRefundItems', // done
            9 => 'migrateBizOrderLog',
            10 => 'migrateBizPaymentTrade', // done
            11 => 'migrateBizPaymentTradeFromCashOrder', // done
            12 => 'migrateBizSecurityAnswer', // done
            13 => 'migrateBizPayAccount',   // done
            14 => 'migrateBizUserBalance',  // done
            15 => 'migrateBizUserCashflow',
            16 => 'registerJobs', // done
            17 => 'migrateJoinMemberOperationRecord',
            18 => 'migrateExitMemberOperationRecord',
        );

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

    protected function migrateBizOrders()
    {
        $this->addMigrateId('biz_order');

        $connection = $this->getConnection();
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
                `totalPrice`*100 as `price_amount`,
                `priceType` as `price_type`,
                floor((`amount` + `coinAmount`/coinRate)*100) as `pay_amount`,
                `userId` as `user_id`,
                '' as `callback`,
                `sn` as `trade_sn`,
                case when `status` in ('paid', 'refunding') then 'success' when `status` = 'cancelled' then 'closed' else `status` end as `status`,
                `paidTime` as `pay_time`,
                case when `payment` in ('alipay', 'coin', 'heepay', 'llpay', 'none', 'quickpay', 'wxpay') then `payment` else 'none' end as `payment`,
                `paidTime` as `finish_time`,
                0 as `close_time`, -- TODO 当订单关闭状态时的时间, 从日志中取得
                '' as `close_data`, -- TODO 当订单关闭状态时的数据, 从日志中取得
                0 as `close_user_id`, -- TODO 当订单关闭状态时的操作人, 从日志中取得
                0 as `seller_id`,
                `userId` as `created_user_id`, -- TODO 创建订单者
                `data` as `create_extra`,
                '' as `device`, -- TODO 处理device字段, 下单设备：app, pc, 手机
                `amount`*100 as `paid_cash_amount`,
                `coinAmount`*100 as `paid_coin_amount`,
                `refundEndTime` as `refund_deadline`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `id` as `migrate_id`
            from orders where id not in (select migrate_id from `biz_order`);
        ");

        $connection->exec("update biz_order set source = 'marketing' where migrate_id in (select id from orders where payment='marketing');");
        $connection->exec("update biz_order set source = 'outside' where migrate_id in (select id from orders where payment='outside');");

        $connection->exec("update biz_order set payment = 'lianlianpay' where payment = 'llpay';");
        $connection->exec("update biz_order set payment = 'wechat' where payment = 'wxpay';");

        return 1;
    }

    protected function migrateBizOrderItems()
    {
        $this->addMigrateId('biz_order_item');

        $connection = $this->getConnection();
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
                `o`.`status` as `status`, -- 保持和biz_order一样
                `o`.`refundId` as `refund_id`,
                case when re.status = 'refunded' then 'refunded' else '' end as `refund_status`, -- 当有refund_id时，冗余的退款状态
                `o`.`totalPrice`*100 as `price_amount`,
                case when (o.`totalPrice`*100 - o.`couponDiscount`*100 - o.`discount`*100) < 0 then 0 else (o.`totalPrice`*100 - o.`couponDiscount`*100 - o.`discount`*100) end as `pay_amount`, -- 应付款
                `o`.`targetId` as `target_id`,
                `o`.`targetType` as `target_type`,
                `o`.`paidTime` as `pay_time`,
                `o`.`paidTime` as `finish_time`,
                0 as `close_time`, -- 关闭时间,保持和biz_order
                `o`.`userId` as `user_id`,
                0 as `seller_id`,
                '' as `create_extra`,
                '' as `snapshot`,
                `o`.`createdTime` as `created_time`,
                `o`.`updatedTime` as `updated_time`,
                `o`.`id` as `migrate_id`
            from orders o left join order_refund re on o.refundId = re.id where `o`.`id` not in (select migrate_id from `biz_order_item`);
        ");

        // 处理会员订单
        $vipOrders = $connection->fetchAll("select `id`, `data` from orders where targetType='vip' and id not in (select migrate_id from `biz_order_item` where unit<>'' and target_type='vip');");
        foreach ($vipOrders as $vipOrder) {
            $data = json_decode($vipOrder['data'], true);
            $connection->exec("update biz_order_item set num = '{$data['duration']}', unit = '{$data['unitType']}' where migrate_id = {$vipOrder['id']} and target_type = 'vip';");
        }

        // 处理status
        $connection->exec("update biz_order_item oi set status = (select status from biz_order where id = oi.order_id);");

        // 处理close_time
        $connection->exec("update biz_order_item boi set close_time = (select close_time from biz_order where id = boi.order_id);");

        return 1;
    }

    protected function migrateBizOrderItemDeductsByCoupon()
    {
        $this->addMigrateId('biz_order_item_deduct');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_order_item_deduct` (
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
                o.`couponDiscount`*100 as `deduct_amount`,
                o.`status` as `status`,
                o.`userId` as `user_id`,
                0 as `seller_id`,
                '' as `snapshot`,
                o.`createdTime` as `created_time`,
                o.`updatedTime` as `updated_time`,
                o.`id` as `migrate_id`
            from orders o left join coupon c on o.coupon = c.code where o.coupon is not null and c.id is not null and o.id not in (select migrate_id from `biz_order_item_deduct` where `deduct_type` = 'coupon');
        ");

        // 处理status
        $connection->exec("update biz_order_item oi set status = (select status from biz_order where id = oi.order_id);");

        return 1;
    }

    protected function migrateBizOrderItemDeductsByDiscount()
    {
        $this->addMigrateId('biz_order_item_deduct');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_order_item_deduct` (
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
                `discount`*100 as `deduct_amount`,
                `status` as `status`,
                `userId` as `user_id`,
                0 as `seller_id`,
                '' as `snapshot`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `id` as `migrate_id`
            from orders where discountId > 0 and id not in (select migrate_id from `biz_order_item_deduct` where `deduct_type` = 'discount');
        ");

        return 1;
    }

    protected function migrateBizOrderItemDeductsStatus()
    {
        $connection = $this->getConnection();
        $connection->exec("update biz_order_item_deduct oi set status = (select status from biz_order where id = oi.order_id);");
    }

    protected function migrateBizOrderRefund()
    {
        $this->addMigrateId('biz_order_refund');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_order_refund` (
                `id`,
                `title`,
                `order_id`,
                `order_item_id`,
                `sn`,
                `user_id`,
                `reason`,
                `amount`,
                `currency`,
                `deal_time`,
                `deal_user_id`,
                `status`,
                `deal_reason`,
                `created_user_id`,
                `refund_cash_amount`,
                `refund_coin_amount`,
                `created_time`,
                `updated_time`,
                `migrate_id`
            )
            select 
                `id`,
                '' as `title`,
                `orderId` as `order_id`,
                `orderId` as `order_item_id`,
                concat(`createdTime`, FLOOR(RAND() * 10000)) as `sn`,
                `userId` as `user_id`,
                `reasonNote` as `reason`,
                `expectedAmount` as `amount`,
                'CYN' as `currency`,
                `updatedTime` as `deal_time`,
                `operator` as `deal_user_id`,
                `status`,
                '' as `deal_reason`,
                `userId` as `created_user_id`,
                `actualAmount` as `refund_cash_amount`,
                0 as `refund_coin_amount`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `id` as `migrate_id`
            from `order_refund` where `id` not in (select migrate_id from `biz_order_refund`)
        ");

        return 1;
    }

    protected function migrateBizOrderRefundItems()
    {
        $this->addMigrateId('biz_order_item_refund');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_order_item_refund` (
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
                `migrate_id`
            )
            select 
                `id`,
                `id` as `order_refund_id`,
                `orderId` as `order_id`,
                `orderId` as `order_item_id`,
                `userId` as `user_id`,
                `expectedAmount` as `amount`,
                0 as `coin_amount`,
                `status` as `status`,
                `userId` as `created_user_id`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `id` as `migrate_id`
            from `order_refund` where `id` not in (select migrate_id from `biz_order_item_refund`)
        ");

        return 1;
    }

    protected function migrateBizOrderLog()
    {
        // TODO

        return 1;
    }

    protected function migrateBizPaymentTrade()
    {
        $this->addMigrateId('biz_payment_trade');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_payment_trade` (
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
                `id`,
                `title`,
                `sn` as `trade_sn`,
                `sn` as `order_sn`,
                `payment` as `platform`,
                '' as `platform_sn`,
                `status` as `status`,
                'money' as `price_type`,
                'CNY' as `currency`,
                `amount`,
                `coinAmount` as `coin_amount`,
                `amount` as `cash_amount`,
                `coinRate` as `rate`,
                'purchase' as `type`,
                `paidTime` as `pay_time`,
                0 as `seller_id`,
                `userId` as `user_id`,
                '' as `notify_data`,
                '' as `platform_created_result`,
                0 as `apply_refund_time`,
                0 as `refund_success_time`,
                '' as `platform_created_params`,
                '' as `platform_type`,
                `updatedTime` as `updated_time`,
                `createdTime` as `created_time`,
                `id` as `migrate_id`
            from `orders` where `id` not in (select migrate_id from `biz_payment_trade` where `type` = 'purchase')
        ");

        return 1;
    }

    protected function migrateBizPaymentTradeFromCashOrder()
    {
        $this->addMigrateId('biz_payment_trade');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_payment_trade` (
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
                `amount`,
                '0' as `coin_amount`,
                `amount` as `cash_amount`,
                '1' as `rate`,
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
            from `cash_orders` where `id` not in (select migrate_id from `biz_payment_trade` where `type` = 'recharge')
        ");

        return 1;
    }

    protected function migrateBizSecurityAnswer()
    {
        $this->addMigrateId('biz_security_answer');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_security_answer` (
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
            from user_secure_question where id not in (select migrate_id from biz_security_answer)
        ");

        return 1;
    }

    protected function migrateBizPayAccount()
    {
        $this->addMigrateId('biz_pay_account');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_pay_account` (
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
            from `user` u where u.`id` not in (select `migrate_id` from `biz_pay_account`)
        ");

        return 1;
    }

    protected function migrateBizUserBalance()
    {
        $this->addMigrateId('biz_user_balance');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_user_balance` (
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
              case when ca.`cash` is null then 0 else ca.`cash` end as `amount`,
              u.`createdTime` as `created_time`,
              u.`updatedTime` as `updated_time`,
              u.`id` as `migrate_id`
            from `user` u left join `cash_account` ca on u.`id` = ca.`userId`  where u.`id` not in (select `migrate_id` from `biz_user_balance`)
        ");

        $sql = "select * from `biz_user_balance` where user_id = 0;";
        $result = $this->getConnection()->fetchAssoc($sql);
        if (empty($result)) {
            $currentTime = time();
            $connection->exec("insert into `biz_user_balance` (`user_id`, `created_time`, `updated_time`) values (0, {$currentTime}, {$currentTime});");
        }

        return 1;
    }

    protected function migrateBizUserCashflow()
    {
        // TODO
        $this->addMigrateId('biz_user_cashflow');

        $connection = $this->getConnection();
//        $connection->exec("
//            insert into `biz_user_cashflow` (
//                `id`,
//                `title`,
//                `sn`,
//                `parent_sn`,
//                `user_id`,
//                `buyer_id`,
//                `type`,
//                `amount`,
//                `currency`,
//                `user_balance`,
//                `order_sn`,
//                `trade_sn`,
//                `platform`,
//                `amount_type`,
//                `created_time`,
//                `migrate_id`
//            )
//            select
//            from `user_flow` uf
//        ");

        return 1;
    }

    protected function registerJobs()
    {
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

        return 1;
    }

    protected function migrateJoinMemberOperationRecord()
    {
        $connection = $this->getConnection();
        $connection->exec("
            insert into `member_operation_record` (
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
            from `orders` where status = 'paid' and `id` not in (select `order_id` from `member_operation_record` where `operate_type` = 'join')
        "); 

        return 1;       
    }

    protected function migrateExitMemberOperationRecord()
    {
        $connection = $this->getConnection();
        $connection->exec("
            insert into `member_operation_record` (
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
            from `order_refund` where status = 'success' and `orderId` not in (select `order_id` from `member_operation_record` where `operate_type` = 'exit');
        ");

        $connection->exec(
            "UPDATE `member_operation_record` as `mor` , `orders` SET `mor`.`title` = `orders`.`title` where `mor`.`order_id` = `orders`.`id` and `operate_type` = 'exit';"  
        );

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
                  `price_amount` INT(10) unsigned NOT NULL COMMENT '订单总金额',
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
                `id` int(11) UNSIGNED NOT NULL,
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
                `created_time` int(10) UNSIGNED NOT NULL DEFAULT '0'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;        
            ");
        }

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
