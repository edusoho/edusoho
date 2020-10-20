<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    const PAGINATOR_LIMIT = 200;

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
            $dir = realpath($this->biz['kernel.root_dir'] . '/../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger('error', $e->getTraceAsString());
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'processCourseGoodsAndProduct',
            'processClassroomGoodsAndProduct',
            'processReviews',
            'processFavorites',
            'tableOrderItemAddTargetIdBefore',
            'processCourseOrderItems',
            'processClassroomOrderItems',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');
            $this->deleteCache();

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if (1 == $page) {
            ++$step;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }
    }

    protected function processCourseGoodsAndProduct($page)
    {
        $paginator = $this->getPaginator('course_goods_paginator', "SELECT COUNT(id) FROM course_set_v8 WHERE type != 'reservation' AND parentId = 0;");
        $currentPage = $paginator['currentPage'] + 1;

        $this->logger('info', "迁移课程商品：{$currentPage}/{$paginator['totalPage']}");

        $courseSetIds = $this->getConnection()->fetchAll("
            SELECT id FROM course_set_v8 WHERE type != 'reservation' AND parentId = 0 ORDER BY id ASC LIMIT {$paginator['start']}, {$paginator['limit']};
        ");
        if (empty($courseSetIds)) {
            return 1;
        }

        $courseSetIds = array_column($courseSetIds, 'id');
        $implodeCourseSetIds = implode(',', $courseSetIds);
        $courses = $this->getConnection()->fetchAll("
            SELECT id, courseSetId FROM course_v8 WHERE courseSetId IN ({$implodeCourseSetIds})
        ");
        $products = $this->syncCourseProductsByTargetIds($courseSetIds);
        $goods = $this->syncCourseGoodsByProductIds(array_column($products, 'id'));
        $this->syncCourseGoodsSpecsByTargetIdsAndGoodsIds(array_column($courses, 'id'), array_column($goods, 'id'));

        $paginator = $this->setPaginator('course_goods_paginator', $paginator);
        if (empty($paginator)) {
            return 1;
        }

        return $paginator['currentPage'] + 1;
    }

    protected function processClassroomGoodsAndProduct($page)
    {
        $paginator = $this->getPaginator('classroom_goods_paginator', "SELECT COUNT(id) FROM classroom;");
        $currentPage = $paginator['currentPage'] + 1;
        $this->logger('info', "迁移班级商品：{$currentPage}/{$paginator['totalPage']}");

        $classrooms = $this->getConnection()->fetchAll("
            SELECT id FROM classroom ORDER BY id ASC LIMIT {$paginator['start']}, {$paginator['limit']};
        ");
        if (empty($classrooms)) {
            return 1;
        }

        $classroomIds = array_column($classrooms, 'id');
        $products = $this->syncClassroomProductsByTargetIds($classroomIds);
        $goods = $this->syncClassroomGoodsByProductIds(array_column($products, 'id'));
        $this->syncClassroomGoodsSpecsByTargetIdsAndGoodsId($classroomIds, array_column($goods, 'id'));

        $paginator = $this->setPaginator('classroom_goods_paginator', $paginator);
        if (empty($paginator)) {
            return 1;
        }

        return $paginator['currentPage'] + 1;
    }

    protected function processReviews($page)
    {
        $this->logger('info', '处理课程评价');
        $this->getConnection()->exec("
            UPDATE review r INNER JOIN (
                SELECT 
                    g.id AS goodsId, c.id AS courseId, c.courseSetId as courseSetId, c.parentId, cs.title 
                FROM goods g, course_v8 c, course_set_v8 cs, product p 
                WHERE c.courseSetId = cs.id AND cs.id = p.targetId AND p.targetType = 'course' AND g.productId = p.id AND c.parentId = 0
            ) m ON m.courseId = r.targetId AND r.targetType = 'course'
            SET r.targetId = m.goodsId, r.targetType = 'goods';
        ");

        $this->logger('info', '处理班级评价');
        $this->getConnection()->exec("
            UPDATE review r INNER JOIN (
                SELECT
                    g.id AS goodsId, c.id AS classroomId, c.title 
                FROM goods g, classroom c, product p 
                WHERE c.id = p.targetId AND p.targetType = 'classroom' AND g.productId = p.id 
            ) m ON m.classroomId = r.targetId AND r.targetType = 'classroom'
            SET r.targetId = m.goodsId, r.targetType = 'goods';
        ");

        return 1;
    }

    protected function processFavorites($page)
    {
        $updateTime = $this->getConnection()->fetchColumn("
            SELECT createdTime FROM cloud_app_logs WHERE name='EduSoho主系统' AND status = 'SUCCESS' AND toVersion = '8.7.11' ORDER BY createdTime DESC LIMIT 1
        ");

        $this->logger('info', '处理课程收藏');
        if (empty($updateTime)) {
            $this->getConnection()->exec("
                UPDATE favorite f INNER JOIN (
                SELECT gs.goodsId, gs.targetId AS courseId FROM goods_specs gs 
                    INNER JOIN goods g INNER JOIN course_v8 c 
                    ON gs.goodsId = g.id AND g.type = 'course' AND gs.targetId = c.id
                ) m ON m.courseId = f.targetId AND f.targetType = 'course'
                SET f.targetId = m.goodsId, f.targetType = 'goods';
            ");

            return 1;
        }

        $this->getConnection()->exec("
            UPDATE favorite f INNER JOIN (
                SELECT gs.goodsId, gs.targetId AS courseId FROM goods_specs gs 
                    INNER JOIN goods g INNER JOIN course_v8 c 
                    ON gs.goodsId = g.id AND g.type = 'course' AND gs.targetId = c.id
            ) m ON m.courseId = f.targetId AND f.targetType = 'course' AND f.createdTime <= {$updateTime}
            SET f.targetId = m.goodsId, f.targetType = 'goods';
        ");

        $this->getConnection()->exec("
            UPDATE favorite f INNER JOIN (
                SELECT g.id AS goodsId, p.targetId AS courseSetId FROM goods g INNER JOIN product p 
            		ON g.productId = p.id AND g.type = 'course' AND p.targetType = 'course'
            ) m ON m.courseSetId = f.targetId AND f.targetType = 'course' AND f.createdTime > {$updateTime}
            SET f.targetId = m.goodsId, f.targetType = 'goods';
        ");

        $newCreatedTime = $updateTime + 1;
        $this->getConnection()->exec("
            UPDATE favorite f INNER JOIN course_v8 c 
            ON f.targetId = c.id AND f.targetType = 'course' AND f.createdTime <= {$updateTime}
            SET f.targetId = c.courseSetId, f.createdTime = {$newCreatedTime}
        ");

        return 1;
    }

    protected function tableOrderItemAddTargetIdBefore($page)
    {
        if (!$this->isFieldExist('biz_order_item', 'target_id_before')) {
            $this->logger('info', "biz_order_item ADD `target_id_before`");
            $this->getConnection()->exec("
                ALTER TABLE `biz_order_item` ADD `target_id_before` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '商品剥离前的course/classroom id' AFTER `target_id`;
            ");
        }

        return 1;
    }

    protected function processCourseOrderItems($page)
    {
        $paginator = $this->getPaginator('course_order_item_paginator',
            "
                        SELECT COUNT(id) FROM biz_order_item WHERE target_type = 'course';
        ");
        $currentPage = $paginator['currentPage'] + 1;

        $this->logger('info', "更新课程订单：{$currentPage}/{$paginator['totalPage']}");
        $itemIds = $this->getConnection()->fetchAll("
            SELECT id FROM  biz_order_item WHERE target_type = 'course' ORDER BY id ASC LIMIT {$paginator['start']}, {$paginator['limit']};
        ");
        if (empty($itemIds)) {
            return 1;
        }

        $implodeItemIds = implode(',', array_column($itemIds, 'id'));
        $this->logger('info', "更新课程订单的target_id(itemIds:{$implodeItemIds})");
        $this->getConnection()->exec("
            UPDATE biz_order_item o INNER JOIN (
                SELECT gs.id AS skuId, gs.goodsId, gs.targetId AS courseId FROM goods_specs gs 
                INNER JOIN goods g INNER JOIN course_v8 c 
                ON gs.goodsId = g.id AND g.type = 'course' AND gs.targetId = c.id
            ) m ON m.courseId = o.target_id AND o.target_type = 'course' AND o.target_id_before = 0 AND o.id IN ({$implodeItemIds}) 
            SET o.target_id = m.skuId, o.target_id_before = m.courseId;
        ");

        $paginator = $this->setPaginator('course_order_item_paginator', $paginator);
        if (empty($paginator)) {
            return 1;
        }

        return $paginator['currentPage'] + 1;
    }

    protected function processClassroomOrderItems($page)
    {
        $paginator = $this->getPaginator('classroom_order_item_paginator',
            "
                        SELECT COUNT(id) FROM  biz_order_item WHERE target_type = 'classroom';
        ");
        $currentPage = $paginator['currentPage'] + 1;

        $this->logger('info', "更新班级订单：{$currentPage}/{$paginator['totalPage']}");
        $itemIds = $this->getConnection()->fetchAll("
            SELECT id FROM  biz_order_item WHERE target_type = 'classroom' ORDER BY id ASC LIMIT {$paginator['start']}, {$paginator['limit']};
        ");

        if (empty($itemIds)) {
            return 1;
        }

        $implodeItemIds = implode(',', array_column($itemIds, 'id'));
        $this->logger('info', "更新课程订单的target_id(itemIds:{$implodeItemIds})");
        $this->getConnection()->exec("
            UPDATE biz_order_item o INNER JOIN (
                SELECT gs.id AS skuId, gs.goodsId, gs.targetId AS classroomId FROM goods_specs gs 
                INNER JOIN goods g INNER JOIN classroom c 
                ON gs.goodsId = g.id AND g.type = 'classroom' AND gs.targetId = c.id
            ) m ON m.classroomId = o.target_id AND o.target_type = 'classroom' AND o.target_id_before = 0 AND o.id IN ({$implodeItemIds})    
            SET o.target_id = m.skuId, o.target_id_before = m.classroomId;
        ");

        $paginator = $this->setPaginator('classroom_order_item_paginator', $paginator);
        if (empty($paginator)) {
            return 1;
        }

        return $paginator['currentPage'] + 1;
    }

    protected function syncCourseProductsByTargetIds(array $targetIds)
    {
        if (empty($targetIds)) {
            return [];
        }

        $implodedTargetIds = implode(',', $targetIds);
        $newTargetIds = $targetIds;
        $existedTargetIds = [];

        $this->logger('info', "开始同步课程对应的Product信息(CourseSetIds:{$implodedTargetIds})");
        $existedProducts = $this->getConnection()->fetchAll("
            SELECT id, targetId FROM product WHERE targetId IN ({$implodedTargetIds}) AND targetType = 'course';
        ");

        if (!empty($existedProducts)) {
            $existedTargetIds = array_column($existedProducts, 'targetId');
            $newTargetIds = array_diff($targetIds, $existedTargetIds);
        }

        if (!empty($newTargetIds)) {
            $newTargetIds = implode(',', $newTargetIds);
            $this->logger('info', "开始创建课程对应的Product信息(CourseSetIds:{$newTargetIds})");
            $this->getConnection()->exec("
                    INSERT INTO `product` (`targetType`, `targetId`, `title`, `owner`, `createdTime`, `updatedTime`) 
                    SELECT
                        'course' AS targetType, id, title, creator, createdTime, updatedTime 
                    FROM `course_set_v8` WHERE `id` IN ({$newTargetIds});
            ");
        }

        if (!empty($existedTargetIds)) {
            $existedTargetIds = implode(',', $existedTargetIds);
            $this->logger('info', "开始更新课程对应的Product信息(CourseSetIds:{$existedTargetIds})");
            $this->getConnection()->exec("
                 UPDATE `product` p INNER JOIN `course_set_v8` cs 
                    ON p.targetId IN ({$existedTargetIds}) AND p.targetId = cs.id AND p.targetType = 'course'
                    SET 
                        p.title = cs.title, p.owner = cs.creator, p.createdTime = cs.createdTime, p.updatedTime = cs.updatedTime; 
            ");
        }

        $products = $this->getConnection()->fetchAll("
            SELECT id, targetId FROM product WHERE targetType ='course' AND targetId IN ({$implodedTargetIds});
        ");

        $this->logger('info', '同步课程Product信息成功:' . json_encode($products));
        return $products;
    }

    protected function syncCourseGoodsByProductIds(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }

        $implodedProductIds = implode(',', $productIds);
        $newProductIds = $productIds;
        $existedProductIds = [];

        $this->logger('info', "开始同步课程Product对应的Goods信息(ProductIds:{$implodedProductIds})");
        $existedGoods = $this->getConnection()->fetchAll("
            SELECT id, productId FROM `goods` WHERE productId IN ({$implodedProductIds});
        ");

        if (!empty($existedGoods)) {
            $existedProductIds = array_column($existedGoods, 'productId');
            $newProductIds = array_diff($productIds, $existedProductIds);
        }

        if (!empty($newProductIds)) {
            $newProductIds = implode(',', $newProductIds);
            $this->logger('info', "开始创建课程Product对应的Goods信息(ProductIds:{$newProductIds})");
            $this->getConnection()->exec("
                INSERT INTO `goods` (
                    `productId`, `type`, `title`, `subtitle`, `creator`,
                    `status`, 
                    `summary`, `minPrice`, `maxPrice`, `maxRate`, `images`, `orgId`, `orgCode`, `ratingNum`, 
                    `rating`, `hitNum`, `hotSeq`, `recommendWeight`, `recommendedTime`, `createdTime`, `updatedTime`, 
                    `publishedTime`
                ) SELECT 
                    p.id, 'course' AS type, c.title, c.subtitle, creator, 
                    CASE 
                        WHEN c.status = 'draft' THEN 'created'
                        WHEN c.status = 'closed' THEN 'unpublished'
                        ELSE c.status
                    END AS status,
                    c.summary, c.minCoursePrice, c.maxCoursePrice, c.maxRate, c.cover, c.orgId, c.orgCode, c.ratingNum, 
                    c.rating, c.hitNum, c.hotSeq, c.recommendedSeq, c.recommendedTime, c.createdTime, c.updatedTime, 
                    c.createdTime
                FROM product p, course_set_v8 c 
                WHERE p.targetId = c.id AND p.id IN ({$newProductIds});
            ");
        }

        if (!empty($existedProductIds)) {
            $existedProductIds = implode(',', $existedProductIds);

            $this->logger('info', "开始更新课程Product对应的Goods信息(ProductIds:{$existedProductIds})");
            $this->getConnection()->exec("
                 UPDATE `goods` g INNER JOIN (
                	SELECT 
                        p.id, c.title, c.subtitle, c.creator, 
                        CASE 
                            WHEN c.status = 'draft' THEN 'created'
                            WHEN c.status = 'closed' THEN 'unpublished'
                            ELSE c.status
                        END AS status, 
                        c.summary, c.minCoursePrice AS minPrice, c.maxCoursePrice AS maxPrice, c.maxRate, 
                        c.cover AS images, c.orgId, c.orgCode, c.ratingNum , 
                        c.rating, c.hitNum, c.hotSeq, c.recommendedSeq AS recommendWeight, 
                        c.recommendedTime, c.createdTime, c.updatedTime 
                	FROM product p, course_set_v8 c WHERE p.targetId = c.id AND p.id IN ({$existedProductIds})
                 ) m ON m.id = g.productId  AND g.type = 'course'
                 SET 
                    g.title = m.title, g.subtitle = m.subtitle, g.creator = m.creator, g.status = m.status, 
                    g.summary =m.summary, g.minPrice = m.minPrice, g.maxPrice = m.maxPrice, g.maxRate = m.maxRate, 
                    g.images = m.images, g.orgId = m.orgId, g.orgCode = m.orgCode, g.ratingNum = m.ratingNum,
                    g.rating = m.rating, g.hitNum = m.hitNum, g.hotSeq = m.hotSeq, g.recommendWeight = m.recommendWeight,
                    g.recommendedTime = m.recommendedTime, g.createdTime = m.createdTime, g.updatedTime =m.updatedTime,
                    g.publishedTime = m.createdTime;
            ");
        }

        $goods = $this->getConnection()->fetchAll("
            SELECT id, productId FROM goods WHERE productId IN ({$implodedProductIds});
        ");

        $this->logger('info', '同步课程Goods信息成功:' . json_encode($goods));
        return $goods;
    }

    protected function syncCourseGoodsSpecsByTargetIdsAndGoodsIds($targetIds, $goodsIds)
    {
        if (empty($targetIds) || empty($goodsIds)) {
            return [];
        }

        $implodedCourseIds = implode(',', $targetIds);
        $implodedGoodsIds = implode(',', $goodsIds);
        $newTargetIds = $targetIds;

        $this->logger('info', "开始同步课程Goods对应的GoodsSpecs信息(CourseIds:{$implodedCourseIds}, GoodsIds:{$implodedGoodsIds})");
        $existedGoodsSpecs = $this->getConnection()->fetchAll("
            SELECT id, goodsId, targetId FROM goods_specs WHERE goodsId IN ({$implodedGoodsIds}) AND targetId IN ({$implodedCourseIds})
        ");

        if (!empty($existedGoodsSpecs)) {
            $existedTargetIds = array_column($existedGoodsSpecs, 'targetId');
            $newTargetIds = array_diff($targetIds, $existedTargetIds);
        }

        if (!empty($newTargetIds)) {
            $newTargetIds = implode(',', $newTargetIds);
            $this->logger('info', "开始创建课程计划对应的GoodsSpecs(CourseIds:{$newTargetIds})");
            $this->getConnection()->exec("
                INSERT INTO goods_specs (
                    `goodsId`, `targetId`, 
                    `title`, 
                    `images`, `seq`, `status`, `price`, `coinPrice`, `usageMode`, 
                    `usageDays`,
                    `usageStartTime`, 
                    `usageEndTime`, 
                    `buyableEndTime`, `buyable`, `maxJoinNum`, `services`, `createdTime`, `updatedTime`
                ) SELECT 
                    g.id AS goodsId, c.id AS targetId, 
                    CASE 
                        WHEN c.title = '' OR c.title IS NULL THEN c.courseSetTitle
                        ELSE c.title
                    END AS title,  
                    cs.cover, c.seq, c.status, c.price, c.coinPrice, c.expiryMode,
                    CASE 
                    	WHEN c.expiryDays IS NULL THEN 0 
                    	ELSE c.expiryDays 
                    END, 
                    CASE 
                    	WHEN c.expiryStartDate IS NULL THEN 0 
                    	ELSE c.expiryStartDate 
                    END, 
                    CASE
                     	WHEN c.expiryEndDate IS NULL THEN 0 
                     	ELSE c.expiryEndDate 
                     END, 
                    c.buyExpiryTime, c.buyable, c.maxStudentNum AS maxJoinNum, c.services, c.createdTime, c.updatedTime 
                FROM course_v8 c, product p, goods g, course_set_v8 cs 
                WHERE c.id IN ({$newTargetIds}) AND g.id IN ({$implodedGoodsIds}) 
                    AND p.id = g.productId AND cs.id = p.targetId  AND c.courseSetId = cs.id;
            ");
        }

        if (!empty($existedTargetIds)) {
            $existedTargetIds = implode(',', $existedTargetIds);
            $this->logger('info', "开始更新课程计划对应的GoodsSpecs(CourseIds:{$existedTargetIds})");

            $this->getConnection()->exec("
                UPDATE goods_specs g INNER JOIN (
                    SELECT 
                        g.id AS goodsId, c.id AS targetId, 
                        CASE 
                            WHEN c.title = '' OR c.title IS NULL THEN c.courseSetTitle
                            ELSE c.title
                        END AS title, cs.cover AS images, c.seq, 
                        CASE 
                            WHEN c.status = 'draft' THEN 'created'
                            WHEN c.status = 'closed' THEN 'unpublished'
                            ELSE c.status
                        END AS status, 
                        c.price, c.coinPrice, c.expiryMode AS usageMode, 
                        CASE 
                            WHEN c.expiryDays IS NULL THEN 0 
                            ELSE c.expiryDays 
                        END AS usageDays, 
                        CASE 
                            WHEN c.expiryStartDate IS NULL THEN 0 
                            ELSE c.expiryStartDate 
                        END AS usageStartTime, 
                        CASE
                            WHEN c.expiryEndDate IS NULL THEN 0 
                            ELSE c.expiryEndDate 
                        END AS usageEndTime, 
                        c.buyExpiryTime AS buyableEndTime, c.buyable, c.maxStudentNum AS maxJoinNum, 
                        c.services, c.createdTime, c.updatedTime 
                    FROM course_v8 c, product p, goods g, course_set_v8 cs 
                    WHERE c.id IN ({$existedTargetIds}) AND g.id IN ({$implodedGoodsIds}) AND p.id = g.productId 
                        AND cs.id = p.targetId  AND c.courseSetId = cs.id
                ) m ON m.goodsId = g.goodsId AND m.targetId = g.targetId 
                SET 
                    g.title = m.title, g.images = m.images, g.seq = m.seq, g.status = m.status, g.price = m.price, 
                    g.coinPrice = m.coinPrice, g.usageMode = m.usageMode, g.usageDays = m.usageDays, 
                    g.usageStartTime = m.usageStartTime, g.usageEndTime = m.usageEndTime, 
                    g.buyableEndTime = m.buyableEndTime, g.buyable = m.buyable, g.maxJoinNum = m.maxJoinNum, 
                    g.services = m.services, g.createdTime = m.createdTime, g.updatedTime = m.updatedTime;
            ");
        }

        $goodsSpecs = $this->getConnection()->fetchAll("
            SELECT id, goodsId, targetId FROM goods_specs WHERE goodsId IN ({$implodedGoodsIds}) AND targetId in ({$implodedCourseIds})
        ");

        $this->logger('info', '同步课程GoodsSpecs信息成功: ' . json_encode($goodsSpecs));
        return $goodsSpecs;
    }

    protected function syncClassroomProductsByTargetIds($targetIds)
    {
        if (empty($targetIds)) {
            return [];
        }

        $implodedTargetIds = implode(',', $targetIds);
        $newTargetIds = $targetIds;
        $existedTargetIds = [];

        $this->logger('info', "开始同步班级对应的Product信息(ClassroomIds:{$implodedTargetIds})");
        $existedProducts = $this->getConnection()->fetchAll("
            SELECT id, targetId FROM `product` WHERE targetId IN ({$implodedTargetIds}) AND targetType = 'classroom';
        ");

        if (!empty($existedProducts)) {
            $existedTargetIds = array_column($existedProducts, 'targetId');
            $newTargetIds = array_diff($targetIds, $existedTargetIds);
        }

        if (!empty($newTargetIds)) {
            $newTargetIds = implode(',', $newTargetIds);
            $this->logger('info', "开始创建班级对应的Product信息(ClassroomIds:{$newTargetIds})");
            $this->getConnection()->exec("
                INSERT INTO product (targetType, targetId, title, owner, createdTime, updatedTime) 
                    SELECT
                        'classroom' AS targetType, id , title, creator, createdTime, updatedTime 
                    FROM classroom WHERE id IN ({$newTargetIds});
            ");
        }

        if (!empty($existedTargetIds)) {
            $existedTargetIds = implode(',', $existedTargetIds);
            $this->logger('info', "开始更新班级对应的Product信息(ClassroomIds:{$existedTargetIds})");
            $this->getConnection()->exec("
                UPDATE `product` p INNER JOIN classroom cs 
                    ON cs.id = p.targetId AND p.targetId IN ({$existedTargetIds}) AND p.targetType = 'classroom'
                    SET 
                        targetType='classroom', p.title=cs.title, p.owner=cs.creator, 
                        p.createdTime=cs.createdTime, p.updatedTime=cs.updatedTime;     
            ");
        }

        $products = $this->getConnection()->fetchAll("
            SELECT id, targetId FROM product WHERE targetType ='classroom' AND targetId IN ({$implodedTargetIds});
        ");

        $this->logger('info', '同步班级Product信息成功:' . json_encode($products));
        return $products;
    }

    protected function syncClassroomGoodsByProductIds(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }

        $implodedProductIds = implode(',', $productIds);
        $newProductIds = $productIds;
        $existedProductIds = [];

        $this->logger('info', "开始同步班级Product对应的Goods信息(ProductIds:{$implodedProductIds})");
        $existedGoods = $this->getConnection()->fetchAll("
            SELECT id, productId FROM `goods` WHERE productId IN ({$implodedProductIds});
        ");

        if (!empty($existedGoods)) {
            $existedProductIds = array_column($existedGoods, 'productId');
            $newProductIds = array_diff($productIds, $existedProductIds);
        }

        if (!empty($newProductIds)) {
            $newProductIds = implode(',', $newProductIds);
            $this->logger('info', "开始创建班级Product对应的Goods信息(ProductIds:{$newProductIds})");
            $this->getConnection()->exec("
                INSERT INTO `goods` (
                    `productId`, `type`, `title`, `subtitle`, `creator`,
                    `status`,
                    `showable`, `buyable`, `summary`, `minPrice`, `maxPrice`, `maxRate`,
                    `images`,
                    `orgId`, `orgCode`, `ratingNum`, `rating`, `hitNum`, `hotSeq`, `recommendWeight`, `recommendedTime`, 
                    `createdTime`, `updatedTime`, `publishedTime`
                ) SELECT 
                    p.id, 'classroom' AS type, c.title, c.subtitle, creator, 
                    CASE 
                        WHEN c.status = 'draft' THEN 'created'
                        WHEN c.status = 'closed' THEN 'unpublished'
                        ELSE c.status
                    END AS status, 
                    c.showable, c.buyable, c.about, c.price, c.price, c.maxRate, 
                    CASE 
                        WHEN c.smallPicture != '' 
                            THEN CONCAT('{\"small\":\"', c.smallPicture,'\",\"middle\":\"',  c.middlePicture, '\",\"large\":\"', c.largePicture, '\"}')
                        ELSE ''
                    END AS images,
                    c.orgId, c.orgCode, c.ratingNum, c.rating, c.hitNum, c.hotSeq, c.recommendedSeq, c.recommendedTime, 
                    c.createdTime, c.updatedTime, c.createdTime 
                FROM product p, classroom c 
                WHERE p.targetId = c.id AND p.id in ({$newProductIds});
            ");
        }

        if (!empty($existedProductIds)) {
            $existedProductIds = implode(',', $existedProductIds);
            $this->logger('info', "开始更新班级Product对应的Goods信息(ProductIds:{$existedProductIds})");
            $this->getConnection()->exec("
                 UPDATE `goods` g INNER JOIN (
                	SELECT 
                        p.id AS productId, c.title, c.subtitle, creator, 
                        CASE 
                            WHEN c.status = 'draft' THEN 'created'
                            WHEN c.status = 'closed' THEN 'unpublished'
                            ELSE c.status
                        END AS status, 
                        CASE 
                            WHEN c.smallPicture != '' 
                                THEN CONCAT('{\"small\":\"', c.smallPicture,'\",\"middle\":\"',  c.middlePicture, '\",\"large\":\"', c.largePicture, '\"}')
                            ELSE ''
                        END AS images,
                        c.showable, c.buyable, 
                        c.about AS summary, c.price AS minPrice, c.price AS maxPrice, c.maxRate, c.orgId, c.orgCode, 
                        c.ratingNum, c.rating, c.hitNum, c.hotSeq, c.recommendedSeq AS recommendWeight, c.recommendedTime, 
                        c.createdTime, c.updatedTime, c.createdTime AS publishedTime 
                	FROM product p, classroom c 
                	WHERE p.targetId = c.id AND p.id IN ({$implodedProductIds})
                 ) m ON m.productId = g.productId AND g.type = 'classroom'
                 SET 
                    g.title = m.title, g.subtitle = m.subtitle, g.creator = m.creator, g.status = m.status, 
                    g.images = m.images, g.showable = m.showable, g.buyable = m.buyable, g.summary = m.summary, 
                    g.minPrice = m.minPrice, g.maxPrice = m.maxPrice, g.maxRate = m.maxRate, g.orgId = m.orgId, 
                    g.orgCode = m.orgCode, g.ratingNum = m.ratingNum, g.rating = m.rating, g.hitNum = m.hitNum, 
                    g.hotSeq = m.hotSeq, g.recommendWeight = m.recommendWeight, g.recommendedTime = m.recommendedTime, 
                    g.createdTime = m.createdTime, g.updatedTime = m.updatedTime, g.publishedTime = m.publishedTime;
            ");
        }

        $goods = $this->getConnection()->fetchAll("
            SELECT id, productId FROM goods WHERE productId IN ({$implodedProductIds});
        ");

        $this->logger('info', '同步班级Goods信息成功:' . json_encode($goods));
        return $goods;
    }

    protected function syncClassroomGoodsSpecsByTargetIdsAndGoodsId($targetIds, $goodsId)
    {
        if (empty($targetIds) || empty($goodsId)) {
            return [];
        }
        $implodedTargetIds = implode(',', $targetIds);
        $implodedGoodsIds = implode(',', $goodsId);
        $newTargetIds = $targetIds;

        $this->logger('info', "开始同步班级Goods对应的GoodsSpecs信息(ClassroomIds:{$implodedTargetIds}, GoodsIds:{$implodedGoodsIds})");
        $existedGoodsSpecs = $this->getConnection()->fetchAll("
            SELECT id, goodsId, targetId FROM `goods_specs` WHERE goodsId IN ({$implodedGoodsIds}) AND targetId in ({$implodedTargetIds})
        ");

        if (!empty($existedGoodsSpecs)) {
            $existedTargetIds = array_column($existedGoodsSpecs, 'targetId');
            $newTargetIds = array_diff($targetIds, $existedTargetIds);
        }

        if (!empty($newTargetIds)) {
            $newTargetIds = implode(',', $newTargetIds);
            $this->logger('info', "开始创建班级对应的GoodsSpecs(ClassroomIds:{$newTargetIds})");
            $this->getConnection()->exec("
                INSERT INTO goods_specs (
                    `goodsId`, `targetId`, `title`, `price`, `usageMode`, 
                    `status`,
                    `images`, 
                    `usageDays`, 
                    `usageEndTime`, 
                    `buyable`, `services`, `createdTime`, `updatedTime`                    
                ) 
                SELECT 
                    g.id AS goodsId, c.id AS targetId, c.title, c.price, c.expiryMode, 
                    CASE 
                        WHEN c.status = 'draft' THEN 'created'
                        WHEN c.status = 'closed' THEN 'unpublished'
                        ELSE c.status
                    END AS status, 
                    CASE 
                        WHEN c.smallPicture != '' 
                            THEN CONCAT('{\"small\":\"', c.smallPicture,'\",\"middle\":\"',  c.middlePicture, '\",\"large\":\"', c.largePicture, '\"}')
                        ELSE ''
                    END AS images,
                    CASE 
                        WHEN  c.expiryMode = 'days' THEN c.expiryValue
                        ELSE 0
                    END AS usageDays, 
                    CASE 
                        WHEN c.expiryMode = 'date' THEN c.expiryValue
                        ELSE 0
                    END AS usageEndTime, 
                    c.buyable, c.service, c.createdTime, c.updatedTime 
                FROM classroom c, product p, goods g 
                WHERE c.id IN ({$newTargetIds}) AND g.id IN ({$implodedGoodsIds}) AND p.id = g.productId AND c.id = p.targetId;
            ");
        }

        if (!empty($existedTargetIds)) {
            $existedTargetIds = implode(',', $existedTargetIds);
            $this->logger('info', "开始更新班级对应的GoodsSpecs(ClassroomIds:{$existedTargetIds})");
            $this->getConnection()->exec("
                 UPDATE goods_specs g INNER JOIN (
                    SELECT 
                        g.id AS goodsId, c.id AS targetId, c.title, c.price, c.expiryMode as usageMode, 
                        CASE 
                            WHEN c.status = 'draft' THEN 'created'
                            WHEN c.status = 'closed' THEN 'unpublished'
                            ELSE c.status
                        END AS status,
                        CASE 
                            WHEN c.smallPicture != '' 
                                THEN CONCAT('{\"small\":\"', c.smallPicture,'\",\"middle\":\"',  c.middlePicture, '\",\"large\":\"', c.largePicture, '\"}')
                            ELSE ''
                        END AS images,
                        CASE 
                            WHEN  c.expiryMode = 'days' THEN c.expiryValue
                            ELSE 0
                        END AS usageDays, 
                        CASE 
                            WHEN c.expiryMode = 'date' THEN c.expiryValue
                            ELSE 0
                        END AS usageEndTime,
                        c.buyable, c.service as services, c.createdTime, c.updatedTime 
                    FROM classroom c, product p, goods g
                    WHERE c.id IN ({$existedTargetIds}) AND g.id IN ({$implodedGoodsIds}) AND p.id = g.productId AND c.id = p.targetId
                ) m ON m.goodsId = g.goodsId AND m.targetId = g.targetId 
                SET 
                    g.title = m.title, g.status = m.status, g.price = m.price, g.usageMode = m.usageMode,
                    g.usageDays = m.usageDays, g.usageEndTime = m.usageEndTime, g.buyable = m.buyable, g.images = m.images,
                    g.services = m.services, g.createdTime = m.createdTime, g.updatedTime = m.updatedTime;
            ");
        }

        $goodsSpecs = $this->getConnection()->fetchAll("
            SELECT id, goodsId, targetId FROM goods_specs WHERE goodsId IN ({$implodedGoodsIds}) AND targetId in ({$implodedTargetIds})
        ");

        $this->logger('info', '同步班级GoodsSpecs信息成功: ' . json_encode($goodsSpecs));
        return $goodsSpecs;
    }

    protected function getPaginator($settingKey, $countSql)
    {
        $count = $this->getConnection()->fetchColumn($countSql);
        $default = [
            'currentPage' => 0,
            'total' => intval($count),
            'limit' => self::PAGINATOR_LIMIT,
            'totalPage' => ceil(intval($count) / self::PAGINATOR_LIMIT),
            'start' => 0,
        ];

        $paginator = $this->getCacheService()->get($settingKey);
        return empty($paginator) ? $default : $paginator;
    }

    protected function setPaginator($settingKey, $default)
    {
        $paginator = $this->getCacheService()->get($settingKey);
        $paginator = empty($paginator) ? $default : $paginator;
        $paginator['currentPage'] += 1;

        if ($paginator['currentPage'] * self::PAGINATOR_LIMIT < $paginator['total']) {
            $this->getCacheService()->set($settingKey, [
                'currentPage' => $paginator['currentPage'],
                'total' => $paginator['total'],
                'limit' => $paginator['limit'],
                'totalPage' => $paginator['totalPage'],
                'start' => $paginator['currentPage'] * $paginator['limit'],
            ]);

            return $this->getCacheService()->get($settingKey);
        }

        $this->getCacheService()->clear($settingKey);
        return null;
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
        $sql = "SHOW TABLES LIKE '{
        $table}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger('info', '删除缓存');

        return 1;
    }

    private function getSettingService()
    {
        return new \Biz\System\Service\Impl\SettingServiceImpl($this->biz);
    }

    /**
     * @return \Biz\System\Service\CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }

    /**
     * @return \Biz\Course\Dao\CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    /**
     * @return \Biz\Product\Dao\ProductDao
     */
    protected function getProductDao()
    {
        return $this->createDao('Product:ProductDao');
    }

    /**
     * @return \Biz\Goods\Dao\GoodsDao
     */
    protected function getGoodsDao()
    {
        return $this->createDao('Goods:GoodsDao');
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

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    protected function getBizQuestionDao()
    {
        return $this->createDao('ItemBank:Item:QuestionDao');
    }

    protected function getTestpaperDao()
    {
        return $this->createDao('Testpaper:TestpaperDao');
    }

    protected function getTestpaperItemDao()
    {
        return $this->createDao('Testpaper:TestpaperItemDao');
    }

    protected function getAssessmentDao()
    {
        return $this->createDao('ItemBank:Assessment:AssessmentDao');
    }

    protected function getAssessmentSectionDao()
    {
        return $this->createDao('ItemBank:Assessment:AssessmentSectionDao');
    }

    protected function getAssessmentSectionItemDao()
    {
        return $this->createDao('ItemBank:Assessment:AssessmentSectionItemDao');
    }

    protected function getAnswerSceneDao()
    {
        return $this->createDao('ItemBank:Answer:AnswerSceneDao');
    }

    protected function getExerciseActivityDao()
    {
        return $this->createDao('Activity:ExerciseActivityDao');
    }

    protected function getTestpaperActivityDao()
    {
        return $this->createDao('Activity:TestpaperActivityDao');
    }

    protected function getAttachmentDao()
    {
        return $this->biz->dao('ItemBank:Item:AttachmentDao');
    }
}
