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
            2 => 'migrateBizOrders',
            3 => 'migrateBizOrderItems', // done
            4 => 'migrateBizOrderItemDeductsByCoupon', // done
            5 => 'migrateBizOrderItemDeductsByDiscount', // done
            6 => 'migrateBizOrderRefund',
            7 => 'migrateBizOrderRefundItems',
            8 => 'migrateBizOrderLog',
            9 => 'migrateBizPaymentTrade',
            10 => 'migrateBizPaymentTradeFromCashOrder',
            11 => 'migrateBizSecurityAnswer', // done
            12 => 'migrateBizPayAccount',   // done
            13 => 'migrateBizUserBalance',  // done
            14 => 'migrateBizUserCashflow',
            15 => 'registerJobs',
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
                `display_status`,
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
                '' as `source`,
                '' as `created_reason`,
                `totalPrice`*100 as `price_amount`,
                `priceType` as `price_type`,
                0 as `pay_amount`,  -- 统计amount和coinAmount的总和
                `userId` as `user_id`,
                '' as `callback`,
                `cashSn` as `trade_sn`,
                `status`,
                `paidTime` as `pay_time`,
                `payment`,
                `paidTime` as `finish_time`,
                0 as `close_time`, -- 当订单关闭状态时的时间
                '' as `close_data`, -- 当订单关闭状态时的数据
                0 as `close_user_id`, -- 当订单关闭状态时的操作人
                0 as `seller_id`,
                `created_user_id`, -- 创建订单者
                `data` as `create_extra`,
                '' as `device`,
                `status` as `display_status`
                `amount`*100 as `paid_cash_amount`,
                `coinAmount`*100 as `paid_coin_amount`,
                `refundEndTime` as `refund_deadline`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `id` as `migrate_id`
            from orders where id not in (select migrate_id from `biz_order`);
        ");

        // 处理source字段
        $connection->exec("
            
        ");
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
                `id`,
                `id` as `order_id`,
                `sn` as `sn`,
                `title` as `title`,
                '' as `detail`,
                1 as `num`,
                '' as `unit`, -- 会员订单时的单位：按月、按年
                `status` as `status`,
                `refundId` as `refund_id`,
                case when re.status = 'refunded' then 'refunded' else o.status end as `refund_status`, -- 当有refund_id时，冗余的退款状态
                `totalPrice`*100 as `price_amount`,
                (o.`totalPrce` - o.`couponDiscount` - o.`discount`) as `pay_amount`, -- 应付款
                `targetId` as `target_id`,
                `targetType` as `target_type`,
                `paidTime` as `pay_time`,
                `paidTime` as `finish_time`,
                0 as `close_time`, -- 关闭时间
                `userId` as `user_id`,
                0 as `seller_id`,
                '' as `create_extra`,
                '' as `snapshot`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `id` as `migrate_id`
            from orders o right join order_refund re on o.refundId = re.id where id not in (select migrate_id from `biz_order_item`);
        ");

        // 处理会员订单
        $vipOrders = $connection->fetchAll("select `id`, `data` from orders where targetType='vip';");
        foreach ($vipOrders as $vipOrder) {
            $data = json_decode($vipOrder['data'], true);
            $connection->exec("update biz_order_item set num = '{$data['duration']}', unit = '{$data['unitType']}' where migrate_id = {$vipOrder['id']} and target_type = 'vip';");
        }

        // 处理close_time
        $connection->exec("update biz_order_item boi set close_time = (select close_time from biz_order where id = boi.order_id);");
    }

    protected function migrateBizOrderItemDeductsByCoupon()
    {
        $this->addMigrateId('biz_order_item_deduct');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_order_item_deduct` (
                `id`,
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
                o.`id`,
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
            from orders o right join coupon c on o.coupon = c.code where coupon is not null and id not in (select migrate_id from `biz_order_item_deduct` where `deduct_type` = 'coupon');
        ");
    }

    protected function migrateBizOrderItemDeductsByDiscount()
    {
        $this->addMigrateId('biz_order_item_deduct');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_order_item_deduct` (
                `id`,
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
                `id`,
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
                '' as `sn`,
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
    }

    protected function migrateBizOrderRefundItems()
    {
        $this->addMigrateId('biz_order_refund_item');

        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_order_refund_item` (
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
                `ordeId` as `order_id`,
                `ordeId` as `order_item_id`,
                `userId` as `user_id`,
                `expectedAmount` as `amount`,
                0 as `coin_amount`,
                `status` as `status`,
                `userId` as `created_user_id`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `id` as `migrate_id`
            from `order_refund` where `id` not in (select migrate_id from `biz_order_refund_item`)
        ");
    }

    protected function migrateBizOrderLog()
    {
        // TODO
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
    }

    protected function migrateBizPaymentTradeFromCashOrder()
    {
        // TODO
        $this->addMigrateId('biz_payment_trade');

        $connection = $this->getConnection();
        $connection->exec("
            
        ");
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
            from `user` where u.`id` not in (select `migrate_id` from `biz_pay_account`)
        ");
    }

    protected function migrateBizUserBalance()
    {
        $connection = $this->getConnection();
        $connection->exec("
            insert into `biz_user_balance` (
              `id`,
              `user_id`,
              `amount`,
              `migrate_id`
            )
            select
              `id`,
              u.`id` as `user_id`,
              ca.`cash` as `amount`,
              u.`id` as `migrate_id`
            from `user` u right join `cash_account` ca on u.`id` = ca.`userId`  where u.`id` not in (select `migrate_id` from `biz_user_balance`)
        ");
    }

    protected function migrateBizUserCashflow()
    {
        // TODO
        $this->addMigrateId('biz_user_cashflow');

        $connection = $this->getConnection();
        $connection->exec("
            
        ");
    }

    protected function registerJobs()
    {
        // TODO
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
                  `display_status` varchar(32) NOT NULL DEFAULT 'no_paid' COMMENT '订单显示状态(no_paid,paid,refunding,closed,refunded)',
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

    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
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
