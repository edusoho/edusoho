<?php

use Symfony\Component\Filesystem\Filesystem;
use Biz\Util\PluginUtil;

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
            $this->systemUserId = empty($systemUser['id']) ? 0 : $systemUser['id'];

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
            'migrateAddCouponIndex',
            'migrateBizOrderItemDeductsByCoupon', // done
            'migrateBizOrderItemDeductsByDiscount', // done
            'migrateBizOrderRefund', // done
            'migrateBizOrderRefundItems', // done
            'migrateBizOrderLog',
            'migrateBizPaymentTrade', // done
            'migrateBizPaymentTradeFromCashOrder', // done
            'updateBizPaymentTrade',
            'updateBizPaymentTradePlatforms',
            'migrateBizSecurityAnswer', // done
            'migrateBizPayAccount',   // done
            'migrateBizUserCashflowAsUser',
            'migrateBizUserCashflowAsSiteByCoin',
            'migrateBizUserCashflowAsSiteByMoney',
            'migrateBizUserCashflowPlatform',
            'migrateBizUserBalance',  // done
            'migrateSiteUserBalance',
            'migrateBizUserBalanceRechargeAmountAndPurchaseAmount',  // done
            'registerJobs', // done
            'migrateJoinMemberOperationRecord',
            'migrateExitMemberOperationRecord',
            'stopCrmJobs',
            'updateCourseIsFree',
            'updateUserRoles',
            'removeGroupSell',
            'downloadPlugin',
            'updatePlugin',
            'updateAdminRoles',
            'addRefundDeadline',
            'changeCouponStatus',
            'resetCrontabJobNum',
            'initCoinSetting',
            'updateCloudAppType',
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

    protected function initCoinSetting()
    {
        $coinSetting = $this->getSettingService()->get('coin', array());
        if (!is_array($coinSetting)) {
            $coinSetting = array();
        }
        $defaultSetting = array(
            'coin_enabled' => 0,
            'cash_model' => 'deduction',
            'cash_rate' => 1,
            'coin_name' => '虚拟币',
        );
        $coinSetting = array_merge($defaultSetting, $coinSetting);
        $this->getSettingService()->set('coin', $coinSetting);

        return 1;
    }

    protected function changeCouponStatus()
    {
        $connection = $this->getConnection();  
        $connection->exec('ALTER TABLE `coupon` CHANGE `status` `status` ENUM(\'used\',\'unused\',\'receive\',\'using\') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT \'unused\';');

        return 1;
    }

    protected function addRefundDeadline()
    {
        $connection = $this->getConnection();
        if (!$this->isFieldExist('course_member', 'refundDeadline')) {
            $connection->exec("ALTER TABLE `course_member` ADD COLUMN `refundDeadline`  int(10) NOT NULL DEFAULT '0' COMMENT '退款截止时间' AFTER `deadline`");
        }
        
        if (!$this->isFieldExist('classroom_member', 'refundDeadline')) {
            $connection->exec("ALTER TABLE `classroom_member` ADD COLUMN `refundDeadline`  int(10) NOT NULL DEFAULT '0' COMMENT '退款截止时间' AFTER `deadline`");
        }

        return 1;
    }

    protected function updateAdminRoles()
    {
        $roles = array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN');
        $connection = $this->getConnection();

        foreach ($roles as $role) {
            $sql = "select * from role where code='{$role}';";
            $result = $connection->fetchAssoc($sql);
            if ($result) {
                $data = array_merge(json_decode($result['data']), array('admin_orders', 'admin_order_manage', 'admin_order_refunds', 'admin_order_refunds_manage'));
                $connection->exec("update role set data='".json_encode($data)."' where code='{$role}';");
            }
        }

        return 1;
    }

    protected function downloadPlugin($page)
    {
        $plugin = $this->getUpdatePluginInfo($page);
        if (empty($plugin)) {
            return 1;
        }

        $pluginCode = $plugin[0];
        $pluginPackageId = $plugin[1];

        $this->logger('warning', '检测是否安装'.$pluginCode);
        $pluginApp = $this->getAppService()->getAppByCode($pluginCode);
        if (empty($pluginApp)) {
            $this->logger('warning', '网校未安装'.$pluginCode);
            return $page + 1;
        }
        try {
            $package = $this->getAppService()->getCenterPackageInfo($pluginPackageId);
            if(isset($package['error'])){
                $this->logger('warning', $package['error']);
                return $page + 1;
            }
            $error1 = $this->getAppService()->checkDownloadPackageForUpdate($pluginPackageId);
            $error2 = $this->getAppService()->downloadPackageForUpdate($pluginPackageId);
            $errors = array_merge($error1, $error2);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger( 'warning', $error);
                }
            };
        } catch (\Exception $e) {
            $this->logger('warning', $e->getMessage());
        }
        $this->logger('info', '检测完毕');
        return $page + 1;
    }

    protected function updatePlugin($page)
    {
        $plugin = $this->getUpdatePluginInfo($page);
        if (empty($plugin)) {
            return 1;
        }

        $pluginCode = $plugin[0];
        $pluginPackageId = $plugin[1];

        $this->logger( 'warning', '升级'.$pluginCode);
        $pluginApp = $this->getAppService()->getAppByCode($pluginCode);
        if (empty($pluginApp)) {
            $this->logger('warning', '网校未安装'.$pluginCode);
            return $page + 1;
        }

        try {
            $package = $this->getAppService()->getCenterPackageInfo($pluginPackageId);
            if(isset($package['error'])){
                $this->logger( 'warning', $package['error']);
                return $page + 1;
            }
            $errors = $this->getAppService()->beginPackageUpdate($pluginPackageId, 'install', 0);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger( 'warning', $error);
                }
            }
        } catch (\Exception $e) {
            $this->logger('warning', $e->getMessage());
        }
        $this->logger( 'info', '升级完毕');
        return $page + 1;
    }

    private function getUpdatePluginInfo($page)
    {
        $pluginList = array(
            array(
               'Discount',
               1136,
            ),
            array(
               'Coupon',
                1140
            ),
            array(
                'ChargeCoin',
                1138,
            ),
            array(
                'Vip', 
                1135
            ),
            array(
                'MoneyCard', 
                1137,
            ),
            array(
                'UserImporter',
                1139
            )            
        );

        if (empty($pluginList[$page - 1])) {
            return;
        }

        return $pluginList[$page - 1];
    }

    protected function migrateBizOrders($page)
    {
        $this->addMigrateId('biz_order');

        $this->logger('info', "处理biz_orders数据，当前页码{$page}");

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
                case when totalPrice<0 then 0 else round(`totalPrice`*100) end as `price_amount`,
                `priceType` as `price_type`,
                case when (`amount` + `coinAmount`/coinRate) < 0 then 0 else round((`amount` + `coinAmount`/coinRate)*100) end as `pay_amount`,
                `userId` as `user_id`,
                '' as `callback`,
                `sn` as `trade_sn`, -- trade_sn和sn一致
                case when `status` in ('paid', 'refunding') then 'success' when `status` = 'cancelled' then 'closed' else `status` end as `status`,
                `paidTime` as `pay_time`,
                case when `payment` in ('alipay', 'coin', 'heepay', 'llpay', 'none', 'quickpay', 'wxpay', 'iosiap') then `payment` else 'none' end as `payment`,
                `paidTime` as `finish_time`,
                case when `status` = 'cancelled' then `updatedTime` else 0 end as `close_time`, -- TODO 当订单关闭状态时的时间, 从日志中取得
                '' as `close_data`, -- TODO 当订单关闭状态时的数据, 从日志中取得
                case when `status` = 'cancelled' then {$this->systemUserId} else 0 end as `close_user_id`, -- TODO 当订单关闭状态时的操作人, 从日志中取得
                0 as `seller_id`,
                `userId` as `created_user_id`, -- TODO 创建订单者
                `data` as `create_extra`, 
                '' as `device`, -- TODO 处理device字段, 下单设备：app, pc, 手机
                round(`amount`*100) as `paid_cash_amount`,
                round(`coinAmount`*100) as `paid_coin_amount`,
                `refundEndTime` as `refund_deadline`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `id` as `migrate_id`
            from orders where id not in (select migrate_id from `biz_order`) LIMIT 0, 50000;
        ");

        return $page + 1;
    }

    protected function updateBizOrders($page)
    {
        $connection = $this->getConnection();
        $connection->exec("update biz_order set source = 'marketing' where migrate_id in (select id from orders where payment='marketing');");
        $connection->exec("update biz_order set source = 'outside' where migrate_id in (select id from orders where payment='outside');");

        $connection->exec("update biz_order set payment = 'lianlianpay' where payment = 'llpay';");
        $connection->exec("update biz_order set payment = 'iap' where payment = 'iosiap';");
        $connection->exec("update biz_order set payment = 'wechat' where payment = 'wxpay';");

        $this->logger('info', "更新处理biz_orders数据，当前页码{$page}");

        return 1;
    }

    // TODO LEFT JOIN
    protected function migrateBizOrderItems($page)
    {
        $this->addMigrateId('biz_order_item');

        $this->logger('info', "处理biz_order_item数据，当前页码{$page}");

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
                case when totalPrice<0 then 0 else round(`o`.`totalPrice`*100) end as `price_amount`,
                case when (o.`amount` + o.`coinAmount`/coinRate) < 0 then 0 else round((o.`amount` + o.`coinAmount`/o.`coinRate`)*100) end as `pay_amount`, 
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
            from orders o left join order_refund re on o.refundId = re.id where `o`.`id` not in (select migrate_id from `biz_order_item`) LIMIT 0, 50000;
        ");

        return $page + 1;
    }

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
            $duration = empty($data['duration']) || !is_numeric($data['duration']) ? 0 : $data['duration'];
            
            $unit = empty($data['unitType']) ? 0 : $data['unitType'];
            if ($duration<0) {
                $duration = abs($duration);
            }
            $duration = substr($duration, 0, 9);

            $connection->exec("update biz_order_item set create_extra = '{$buyType}', num = '{$duration}', unit = '{$unit}' where migrate_id = {$vipOrder['id']} and target_type = 'vip';");
        }

        $this->logger('info', "更新处理biz_order_item数据，当前页码{$page}");

        return $page + 1;
    }

    protected function migrateAddCouponIndex($page)
    {
        $this->logger('info', "添加coupon的索引，当前页码{$page}");

        $connection = $this->getConnection();

        if (!$this->isIndexExist('coupon', 'code', 'code')) {
            $connection->exec("ALTER TABLE `coupon` ADD INDEX `code` (`code`);");
        }

        return 1;
    }

    protected function migrateBizOrderItemDeductsByCoupon($page)
    {
        $this->addMigrateId('biz_order_item_deduct');


        $this->logger('info', "处理biz_order_item_deduct的优惠码数据，当前页码{$page}");

        $connection = $this->getConnection();

        // 有可能copon批次会删除，导致老数据不会迁移
        $count = $connection->fetchColumn("SELECT COUNT(o.id) FROM orders o where o.coupon <> '' and o.coupon is not null and o.id NOT IN (SELECT migrate_id FROM `biz_order_item_deduct` WHERE `deduct_type` = 'coupon');");

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
                case when c.id is null then 0 else c.id end as `deduct_id`,               
                round(o.`couponDiscount`*100) as `deduct_amount`,
                case when `o`.`status` in ('paid', 'refunding') then 'success' when `o`.`status` = 'cancelled' then 'closed' else `o`.`status` end  as `status`, -- TODO 保持和biz_order一样
                o.`userId` as `user_id`,
                0 as `seller_id`,
                concat('{\"couponCode\":\'', o.coupon, '\'}') as `snapshot`,
                o.`createdTime` as `created_time`,
                o.`updatedTime` as `updated_time`,
                o.`id` as `migrate_id`
            from orders o left join coupon c on o.coupon = c.code where o.coupon <> '' and o.coupon is not null and o.id not in (select migrate_id from `biz_order_item_deduct` where `deduct_type` = 'coupon') LIMIT 0, {$this->pageSize};
        ");

        return $page + 1;
    }

    protected function migrateBizOrderItemDeductsByDiscount($page)
    {
        $this->addMigrateId('biz_order_item_deduct');

        $this->logger('info', "处理biz_order_item_deduct的打折数据，当前页码{$page}");

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
                case when o.`discount` = 10 then 0 else round(o.`totalPrice` * (10 - o.`discount`) * 10) end as `deduct_amount`,
                case when `o`.`status` in ('paid', 'refunding') then 'success' when `o`.`status` = 'cancelled' then 'closed' else `o`.`status` end  as `status`, -- TODO 保持和biz_order一样
                `userId` as `user_id`,
                0 as `seller_id`,
                '' as `snapshot`,
                `createdTime` as `created_time`,
                `updatedTime` as `updated_time`,
                `id` as `migrate_id`
            from orders o where discountId > 0 and id not in (select migrate_id from `biz_order_item_deduct` where `deduct_type` = 'discount') LIMIT 0, {$this->pageSize};
        ");

        return $page + 1;
    }

    // TODO LEFT JOIN
    protected function migrateBizOrderRefund($page)
    {
        $this->addMigrateId('biz_order_refund');

        $this->logger('info', "处理biz_order_refund的数据，当前页码{$page}");

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
            from `order_refund` `r` left join `orders` `o` on `r`.`orderId`=`o`.`id` where r.`id` not in (select migrate_id from `biz_order_refund`) LIMIT 0, 5000
        ");

        return $page + 1;
    }

    protected function migrateBizOrderRefundItems($page)
    {
        $this->addMigrateId('biz_order_item_refund');

        $this->logger('info', "处理biz_order_item_refund的数据，当前页码{$page}");

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
            from `order_refund` where `id` not in (select migrate_id from `biz_order_item_refund`) LIMIT 0, 5000
        ");

        return $page + 1;
    }

    protected function migrateBizOrderLog($page)
    {
        $this->addMigrateId('biz_order_log');

        $this->logger('info', "处理biz_order_log的数据，当前页码{$page}");

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) from `order_log` where `id` not in (select migrate_id from `biz_order_log`)");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_order_log` (
                `order_id`,
                `status`,
                `user_id`,
                `deal_data`,
                `order_refund_id`,
                `ip`,
                `created_time`,
                `updated_time`,
                `migrate_id`
            )
            SELECT
                `orderId` as `order_id`,
                case when `type` = 'cancelled' then 'order.closed' when `type` = 'created' then 'order.created' when `type` = 'pay_success' then 'order.success' when `type` = 'refund_apply' then 'order_refund.auditing' when `type` = 'refund_cancel' then 'order_refund.cancel' when `type` = 'refund_failed' then 'order_refund.refused' when `type` = 'refund_success' then 'order.refunded' else `type` end as `status`,
                `userId` as `user_id`,
                `data` as `deal_data`,
                '0' as `order_refund_id`,
                `ip`,
                `createdTime` as `created_time`,
                `createdTime` as `updated_time`,
                `id` as `migrate_id`
            FROM order_log WHERE id NOT IN (SELECT migrate_id FROM biz_order_log) LIMIT 0, 50000
        ");

        return $page+1;
    }

    protected function migrateBizPaymentTrade($page)
    {
        $this->addMigrateId('biz_pay_trade');

        $this->logger('info', "处理biz_pay_trade的数据，当前页码{$page}");

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) from `orders` where `id` not in (select migrate_id from `biz_pay_trade` where `type` = 'purchase')");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_pay_trade` (
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
                o.`title`,
                o.`sn` as `trade_sn`,
                o.`sn` as `order_sn`,
                o.`payment` as `platform`,
                '' as `platform_sn`,
                case when o.`status` in ('paid', 'refunding') then 'paid' when o.`status` = 'created' then 'paying' when o.`status` = 'cancelled' then 'closed' else o.`status` end as `status`,
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
            from `orders` o left join `order_refund` r on o.refundId=r.id where o.`id` not in (select migrate_id from `biz_pay_trade` where `type` = 'purchase') LIMIT 0, 30000
        ");

        return $page + 1;
    }

    protected function migrateBizPaymentTradeFromCashOrder($page)
    {
        $this->addMigrateId('biz_pay_trade');

        $this->logger('info', "处理biz_pay_trade的现金订单数据，当前页码{$page}");

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) from `cash_orders` where `id` not in (select migrate_id from `biz_pay_trade` where `type` = 'recharge')");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_pay_trade` (
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
                case when `payment` in ('alipay', 'coin', 'heepay', 'llpay', 'none', 'quickpay', 'wxpay', 'iosiap') then `payment` else 'none' end as `payment`,
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
            from `cash_orders` where `id` not in (select migrate_id from `biz_pay_trade` where `type` = 'recharge') LIMIT 0, {$this->pageSize}
        ");

        return $page + 1;
    }

    protected function updateBizPaymentTrade($page)
    {
        $connection = $this->getConnection();

        $connection->exec("update biz_pay_trade set platform = 'lianlianpay' where platform = 'llpay';");
        $connection->exec("update biz_pay_trade set platform = 'iap' where platform = 'iosiap';");
        $connection->exec("update biz_pay_trade set platform = 'wechat' where platform = 'wxpay';");

        $this->logger('info', "更新处理biz_pay_trade的platform数据，当前页码{$page}");

        return 1;
    }

    protected function migrateBizSecurityAnswer($page)
    {
        $this->addMigrateId('biz_pay_security_answer');

        $this->logger('info', "处理biz_pay_security_answer的数据，当前页码{$page}");

        $connection = $this->getConnection();
        $count = $connection->fetchColumn("SELECT COUNT(id) from user_secure_question where id not in (select migrate_id from biz_pay_security_answer)");
        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_pay_security_answer` (
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
            from user_secure_question where id not in (select migrate_id from biz_pay_security_answer) LIMIT 0, {$this->pageSize}
        ");

        return $page + 1;
    }

    protected function migrateBizPayAccount($page)
    {
        $this->addMigrateId('biz_pay_account');

        $this->logger('info', "处理biz_pay_account的数据，当前页码{$page}");

        $connection = $this->getConnection();

        $count = $connection->fetchColumn("SELECT COUNT(id) from `user` where payPassword<>'' and `id` not in (select `migrate_id` from `biz_pay_account`)");
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
            from `user` u where payPassword<>'' and u.`id` not in (select `migrate_id` from `biz_pay_account`) LIMIT 0, 10000
        ");

        return $page + 1;
    }

    protected function migrateBizUserBalance($page)
    {
        $this->addMigrateId('biz_pay_user_balance');

        $this->logger('info', "处理biz_pay_user_balance的数据，当前页码{$page}");

        $connection = $this->getConnection();
        $count = $connection->fetchColumn("SELECT count(id) FROM `user` where id not in (select `user_id` from `biz_pay_user_balance`)");

        if (empty($count)) {
            return 1;
        }

        $connection->exec("
            INSERT into `biz_pay_user_balance` (
              `user_id`,
              `amount`,
              `created_time`,
              `updated_time`,
              `migrate_id`
            )
            select
              u.`id` as `user_id`,
              case when ca.`cash`*100 is null then 0 else round(ca.`cash`*100) end as `amount`,
              u.`createdTime` as `created_time`,
              u.`updatedTime` as `updated_time`,
              u.`id` as `migrate_id`
            from `user` u left join cash_account ca on u.`id` = ca.`userId`  where u.`id` not in (select `user_id` from `biz_pay_user_balance`) LIMIT 0, 10000
        ");

        return $page + 1;
    }

    protected function migrateSiteUserBalance($page)
    {
        $this->logger('info', "处理biz_pay_user_balance的site数据，当前页码{$page}");

        $connection = $this->getConnection();

        $sql = "select * from `biz_pay_user_balance` where user_id = 0;";
        $result = $this->getConnection()->fetchAssoc($sql);
        if (empty($result)) {
            $currentTime = time();

            $total = $connection->fetchColumn('select case when sum(cash) is not null then sum(cash) else 0 end from cash_account');
            $total = 0 - $total*100;

            $connection->exec("insert into `biz_pay_user_balance` (`user_id`, `amount`, `created_time`, `updated_time`) values (0, {$total}, {$currentTime}, {$currentTime});");
        }

        return $page;
    }

    protected function migrateBizUserBalanceRechargeAmountAndPurchaseAmount($page)
    {
        $connection = $this->getConnection();
        $connection->exec("update biz_pay_user_balance ub, (select user_id, case when sum(amount) is not null then sum(amount) else 0 end amount from biz_pay_cashflow uc where uc.amount_type='coin' and uc.type='outflow' group by user_id) a set ub.purchase_amount = a.amount where ub.user_id=a.user_id;");
        $connection->exec("update biz_pay_user_balance ub, (select user_id, case when sum(amount) is not null then sum(amount) else 0 end amount from biz_pay_cashflow uc where uc.amount_type='coin' and uc.type='inflow' group by user_id) a set ub.recharge_amount = a.amount where ub.user_id =a.user_id;");
        $connection->exec("update biz_pay_user_balance ub set cash_amount = (select case when sum(amount) is not null then sum(amount) else 0 end amount from biz_pay_cashflow uc where uc.amount_type='money' and uc.type='outflow') where ub.user_id = 0;");

        return 1;
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

    // TODO LEFT JOIN
    protected function migrateBizUserCashflow($page, $userIdType, $amountType = '')
    {
        $this->addMigrateId('biz_pay_cashflow');

        $this->logger('info', "处理{$userIdType}的biz_pay_cashflow的数据，当前页码{$page}");

        $connection = $this->getConnection();

        if ($userIdType == 'user') {
            $migrateUserId = 'uf.`userId` as `user_id`,';
            $migrateType = "uf.`type` as `type`,";
            $migrateSn = "uf.`sn` as `sn`,";

            $whereSql = "uf.amount>0 and uf.`id` not in (select `migrate_id` from `biz_pay_cashflow` where user_id<>0)";
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

            $whereSql = "{$whereSql} uf.amount>0 and uf.`id` not in (select `migrate_id` from `biz_pay_cashflow` where user_id=0)";

        }

        $count = $connection->fetchColumn("SELECT COUNT(id) from `cash_flow` uf where {$whereSql}");
        if (empty($count)) {
            return 1;
        }

        $sql = "
            insert into `biz_pay_cashflow` (
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
            from `cash_flow` uf left join orders o on uf.orderSn = o.sn where {$whereSql} LIMIT 0, 10000
        ";

        $connection->exec($sql);

        return $page + 1;
    }

    protected function updateBizPaymentTradePlatforms($page)
    {
        $connection = $this->getConnection();
        $connection->exec("update biz_pay_trade set platform=(select payment from biz_order where sn=order_sn) where type='purchase';");

        $connection->exec("update biz_pay_trade set platform = 'lianlianpay' where platform = 'llpay' and type='recharge';");
        $connection->exec("update biz_pay_trade set platform = 'wechat' where platform = 'wxpay' and type='recharge';");
        return 1;
    }

    protected function migrateBizUserCashflowPlatform($page)
    {
        $connection = $this->getConnection();
        $sql = "update `biz_pay_cashflow` set platform='none' where amount_type='coin';";
        $connection->exec($sql);

        $sql = "update `biz_pay_cashflow` uc set uc.platform=(select platform from biz_pay_trade where trade_sn=uc.trade_sn) where amount_type='money' and trade_sn in (select trade_sn from biz_pay_trade where trade_sn=uc.trade_sn);";
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
                  'Codeages\\\\Biz\\\\Order\\\\Job\\\\CloseExpiredOrdersJob',
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

        $this->logger('info', "处理member_operation_record的加入数据，当前页码{$page}");

        if (!$this->isIndexExist('member_operation_record', 'operate_type', 'operate_type')) {
            $connection->exec("ALTER TABLE `member_operation_record` ADD INDEX operate_type (operate_type);");
        }

        if (!$this->isIndexExist('member_operation_record', 'order_id', 'order_id')) {
            $connection->exec("alter table member_operation_record add index order_id (order_id);");
        }

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
                `userId` as `operator_id`,
                '' as `data`,
                `userId` as `user_id`,
                `id` as `order_id`,
                0 as `refund_id`,
                `note` as `reason`,
                `createdTime` as `created_time`
            from `orders` where status = 'paid' and `id` not in (select `order_id` from `member_operation_record` where `operate_type` = 'join') LIMIT 0, 50000
        "); 

        return $page + 1;       
    }

    protected function migrateExitMemberOperationRecord($page)
    {
        $connection = $this->getConnection();

        $this->logger('info', "处理member_operation_record的退出数据，当前页码{$page}");
        
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
            from `order_refund` where status = 'success' and `orderId` not in (select `order_id` from `member_operation_record` where `operate_type` = 'exit') LIMIT 0, 50000;
        ");

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

    protected function updateUserRoles()
    {
        $connection = $this->getConnection();
        $roles = array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN');

        foreach($roles as $role) {
            $sql = "select * from role where code='{$role}';";
            $result = $connection->fetchAssoc($sql);
            if ($result) {
                $data = array_merge(json_decode($result['data']), array('admin_orders', 'admin_order_manage', 'admin_order_refunds', 'admin_order_refunds_manage'));
                $connection->exec("update role set data='".json_encode($data)."' where code='{$role}';");
            }       
        }

        return 1;
    }

    protected function createTables()
    {
        $connection = $this->getConnection();

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_pay_cashflow` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '标题',
              `sn` VARCHAR(64) NOT NULL COMMENT '账目流水号',
              `parent_sn` VARCHAR(64) COMMENT '本次交易的上一个账单的流水号',
              `user_id` int(10) unsigned NOT NULL COMMENT '账号ID，即用户ID',
              `user_balance` BIGINT(16) NOT NULL DEFAULT '0' COMMENT '账单生成后的对应账户的余额，若amount_type为coin，对应的是虚拟币账户，amount_type为money，对应的是现金庄户余额',
              `buyer_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '买家',
              `type` enum('inflow','outflow') NOT NULL COMMENT '流水类型',
              `action` VARCHAR(32) not null default '' COMMENT 'refund, purchase, recharge',
              `amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '金额',
              `amount_type` VARCHAR(32) NOT NULL COMMENT 'ammount的类型：coin, money',
              `currency` VARCHAR(32) NOT NULL COMMENT '支付的货币: coin, CNY...',
              `order_sn` varchar(64) NOT NULL COMMENT '订单号',
              `trade_sn` varchar(64) NOT NULL COMMENT '交易号',
              `platform` VARCHAR(32) NOT NULL DEFAULT 'none' COMMENT '支付平台：none, alipay, wxpay...',
              `created_time` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='帐目流水';
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_pay_user_balance` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` int(10) unsigned NOT NULL COMMENT '用户',
              `amount` BIGINT(16) NOT NULL DEFAULT '0' COMMENT '账户的虚拟币余额',
              `cash_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '现金余额',
              `locked_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '冻结虚拟币金额',
              `recharge_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '充值总额',
              `purchase_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '消费总额',
              `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_pay_trade` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(1024) NOT NULL COMMENT '标题',
              `trade_sn` varchar(64) NOT NULL COMMENT '交易号',
              `order_sn` varchar(64) NOT NULL COMMENT '客户订单号',
              `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '交易状态',
              `amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '订单的需支付金额',
              `price_type` varchar(32) NOT NULL COMMENT '标价类型，现金支付or虚拟币；money, coin',
              `currency` varchar(32) NOT NULL DEFAULT '' COMMENT '支付的货币类型',
              `coin_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '虚拟币支付金额',
              `cash_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '现金支付金额',
              `rate` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '虚拟币和现金的汇率',
              `type` varchar(32) NOT NULL DEFAULT 'purchase' COMMENT '交易类型：purchase，recharge，refund',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `user_id` INT(10) unsigned NOT NULL COMMENT '买家id',
              `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易时间',
              `apply_refund_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请退款时间',
              `refund_success_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成功退款时间',
              `notify_data` text,
              `platform` varchar(32) NOT NULL DEFAULT '' COMMENT '第三方支付平台',
              `platform_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '第三方支付平台的交易号',
              `platform_type` text COMMENT '在第三方系统中的支付方式',
              `platform_created_result` text,
              `platform_created_params` text COMMENT '在第三方系统创建支付订单时的参数信息',
              `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`trade_sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_pay_account` (
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

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_pay_security_answer` (
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
                `reason_type` varchar(255) NOT NULL default '' COMMENT '用户退出或加入的类型：refund, remove, exit',
                `created_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;        
            ");
        }

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_order` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '订单标题',
              `sn` VARCHAR(64) NOT NULL COMMENT '订单号',
              `price_amount` BIGINT(16) unsigned NOT NULL COMMENT '订单总价',
              `price_type` varchar(32) not null  COMMENT '订单总价的类型，现金支付or虚拟币；money, coin',
              `pay_amount` BIGINT(16) unsigned NOT NULL COMMENT '应付金额',
              `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '订单状态',
              `trade_sn` VARCHAR(64) COMMENT '支付交易号，支付成功后记录',
              `paid_cash_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '付款的现金金额，支付成功后记录',
              `paid_coin_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '付款的虚拟币金额，支付成功后记录',
              `pay_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间，支付成功后记录',
              `payment` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '支付类型，支付成功后记录',
              `finish_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易成功时间',
              `close_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易关闭时间',
              `close_data` TEXT COMMENT '交易关闭描述',
              `close_user_id` INT(10) unsigned DEFAULT '0' COMMENT '关闭交易的用户',
              `expired_refund_days` int(10) unsigned DEFAULT '0' COMMENT '退款的到期天数',
              `refund_deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请退款截止日期',
              `success_data` text COMMENT '交易成功的扩展信息字段',
              `fail_data` text COMMENT '交易失败的扩展信息字段',
              `created_user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单的创建者',
              `create_extra` text COMMENT '创建时的自定义字段，json方式存储',
              `created_reason` TEXT COMMENT '订单创建原因, 例如：导入，购买等',
              `callback` TEXT COMMENT '商品中心的异步回调信息',
              `device` varchar(32) COMMENT '下单设备（pc、mobile、app）',
              `source` VARCHAR(16) NOT NULL DEFAULT 'self' COMMENT '订单来源：网校本身、微营销、第三方系统',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `biz_order_item` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(1024) NOT NULL COMMENT '商品名称',
              `detail` TEXT COMMENT '商品描述',
              `sn` VARCHAR(64) NOT NULL COMMENT '编号',
              `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
              `num` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '数量',
              `unit` varchar(16) COMMENT '单位',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '商品状态',
              `refund_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '最新退款id',
              `refund_status` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '退款状态',
              `price_amount` BIGINT(16) unsigned NOT NULL COMMENT '商品总价格',
              `pay_amount` BIGINT(16) unsigned NOT NULL COMMENT '商品应付金额',
              `target_id` INT(10) unsigned NOT NULL COMMENT '商品id',
              `target_type` VARCHAR(32) NOT NULL COMMENT '商品类型',
              `pay_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
              `finish_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易成功时间',
              `close_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易关闭时间',
              `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `snapshot` text COMMENT '商品快照',
              `create_extra` text COMMENT '创建时的自定义字段，json方式存储',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `biz_order_item_deduct` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
              `detail` TEXT COMMENT '描述',
              `item_id` INT(10) unsigned NOT NULL COMMENT '商品id',
              `deduct_type` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '促销类型',
              `deduct_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '对应的促销活动id',
              `deduct_amount` BIGINT(16) unsigned NOT NULL COMMENT '扣除的价格',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '商品状态',
              `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `snapshot` text COMMENT '促销快照',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `biz_order_refund` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '退款单标题',
              `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
              `order_item_id` INT(10) unsigned NOT NULL COMMENT '退款商品的id',
              `sn` VARCHAR(64) NOT NULL COMMENT '退款订单编号',
              `user_id` INT(10) unsigned NOT NULL COMMENT '退款人',
              `reason` TEXT COMMENT '退款的理由',
              `amount` BIGINT(16) unsigned NOT NULL COMMENT '退款总金额',
              `currency` VARCHAR(32) NOT NULL DEFAULT 'money' COMMENT '货币类型: coin, money',
              `deal_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理时间',
              `deal_user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理人',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '退款状态',
              `deal_reason` TEXT COMMENT '处理理由',
              `refund_cash_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '退款的现金金额',
              `refund_coin_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '退款的虚拟币金额',
              `created_user_id` INT(10) unsigned NOT NULL COMMENT '申请者',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
          CREATE TABLE IF NOT EXISTS `biz_order_item_refund` (
            `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
            `order_refund_id` INT(10) unsigned NOT NULL COMMENT '退款订单id',
            `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
            `order_item_id` INT(10) unsigned NOT NULL COMMENT '订单中的商品的id',
            `target_id` INT(10) unsigned NOT NULL COMMENT '商品id',
            `target_type` VARCHAR(32) NOT NULL COMMENT '商品类型',
            `user_id` INT(10) unsigned NOT NULL COMMENT '退款人',
            `amount` BIGINT(16) unsigned NOT NULL DEFAULT 0 COMMENT '涉及金额',
            `coin_amount` BIGINT(16) unsigned NOT NULL DEFAULT 0 COMMENT '涉及虚拟币金额',
            `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '退款状态',
            `created_user_id` INT(10) unsigned NOT NULL COMMENT '申请者',
            `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
            `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_order_log` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '订单id',
              `status` VARCHAR(32) NOT NULL COMMENT '订单状态',
              `user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户',
              `deal_data` TEXT COMMENT '处理数据',
              `order_refund_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '退款id',
              `ip` VARCHAR(32) not null default '' COMMENT 'ip',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->createIndex('biz_pay_trade', 'type', 'type');

        $this->logger('info', '新建biz表');

        return 1;
    }

    protected function resetCrontabJobNum()
    {
        \Biz\Crontab\SystemCrontabInitializer::init();

        $connection = $this->getConnection();
        $connection->exec("update biz_scheduler_job bsj inner join biz_scheduler_job_fired bsjf on bsj.id=bsjf.job_id set status='failure' where bsjf.status='executing' and bsj.name='Scheduler_MarkExecutingTimeoutJob';");

        return 1;
    }

    protected function removeGroupSell()
    {
        $connection = $this->getConnection();
        $connection->exec("
            DELETE FROM `cloud_app` WHERE `code` = 'GroupSell';
        ");
        PluginUtil::refresh();

        return 1;
    }

    protected  function updateCloudAppType(){
        $connection = $this->getConnection();
        $connection->exec("alter table `cloud_app` MODIFY `type` varchar(64) NOT NULL DEFAULT 'plugin' COMMENT '应用类型(core系统，plugin插件应用, theme主题应用)';");
        $connection->exec(" update cloud_app set type ='core' where code = 'MAIN';");
        return 1;
    }

    protected function addMigrateId($table)
    {
        $connection = $this->getConnection();
        if (!$this->isFieldExist($table, 'migrate_id')) {
            $connection->exec("ALTER TABLE `{$table}` ADD COLUMN `migrate_id` int(10) NOT NULL DEFAULT '0' COMMENT '数据迁移原表id';");
        }

        if (!$this->isIndexExist($table, 'migrate_id', 'migrate_id')) {
            $connection->exec("ALTER TABLE `{$table}` ADD INDEX migrate_id (migrate_id);");
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

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
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
