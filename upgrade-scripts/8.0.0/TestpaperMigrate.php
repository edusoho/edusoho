<?php

class TestpaperMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('testpaper_v8')) {
            $this->getConnection()->exec("
                CREATE TABLE `testpaper_v8` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
                  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷名称',
                  `description` text COMMENT '试卷说明',
                  `courseId` int(10) NOT NULL DEFAULT '0',
                  `lessonId` int(10) NOT NULL DEFAULT '0',
                  `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '限时(单位：秒)',
                  `pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷生成/显示模式',
                  `target` varchar(255) NOT NULL DEFAULT '',
                  `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '试卷状态：draft,open,closed',
                  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '总分',
                  `passedCondition` text,
                  `itemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
                  `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
                  `metas` text COMMENT '题型排序',
                  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id',
                  `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
                  `courseSetId` int(11) unsigned NOT NULL DEFAULT '0',
                  `migrateTestId` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('testpaper_item_v8')) {
            $this->getConnection()->exec("
                CREATE TABLE `testpaper_item_v8` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目',
                  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属试卷',
                  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
                  `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目id',
                  `questionType` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类别',
                  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
                  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分值',
                  `missScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
                  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源testpaper_item的id',
                  `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
                  `migrateItemId` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        $nextPage = $this->insertTestpaper($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }
    }

    private function insertTestpaper($page)
    {
        $sql = "SELECT * FROM testpaper WHERE id NOT IN (SELECT id FROM testpaper_v8 WHERE type = 'testpaper') ORDER BY id LIMIT 0, {$this->perPageCount}; ";

        $testpapers = $this->getConnection()->fetchAll($sql);

        if (empty($testpapers)) {
            return;
        }

        foreach ($testpapers as $testpaper) {
            $courseSetId = 0;
            $lessonId = 0;
            if (!empty($testpaper['target'])) {
                $targetArr = explode('/', $testpaper['target']);
                $courseArr = explode('-', $targetArr[0]);

                if (!empty($targetArr[1])) {
                    $lessonArr = explode('-', $targetArr[1]);
                    $lessonId = intval($lessonArr[1]);
                }
                $courseSetId = intval($courseArr[1]);
            }

            $passedCondition = empty($testpaper['passedStatus']) ? '' : json_encode(array($testpaper['passedStatus']));

            $this->getConnection()->insert('testpaper_v8', array(
                'id' => $testpaper['id'],
                'name' => $testpaper['name'],
                'description' => $testpaper['description'],
                'courseId' => $courseSetId,
                'lessonId' => $lessonId,
                'limitedTime' => $testpaper['limitedTime'],
                'pattern' => 'questionType',
                'target' => $testpaper['target'],
                'status' => $testpaper['status'],
                'score' => $testpaper['score'],
                'passedCondition' => $passedCondition,
                'itemCount' => $testpaper['itemCount'],
                'createdUserId' => $testpaper['createdUserId'],
                'createdTime' => $testpaper['createdTime'],
                'updatedUserId' => $testpaper['updatedUserId'],
                'updatedTime' => $testpaper['updatedTime'],
                'metas' => $testpaper['metas'],
                'copyId' => $testpaper['copyId'],
                'type' => 'testpaper',
                'courseSetId' => $courseSetId,
                'migrateTestId' => $testpaper['id'],
            ));
        }

        return $page + 1;
    }
}
