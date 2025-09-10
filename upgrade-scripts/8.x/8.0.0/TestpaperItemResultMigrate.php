<?php

class TestpaperItemResultMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->perPageCount = 10000;

        if (!$this->isTableExist('testpaper_item_result_v8')) {
            $this->getConnection()->exec("
                CREATE TABLE `testpaper_item_result_v8` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `itemId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷题目id',
                  `testId` int(10) unsigned NOT NULL DEFAULT '0',
                  `resultId` int(10) NOT NULL DEFAULT '0' COMMENT '试卷结果ID',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0',
                  `questionId` int(10) unsigned NOT NULL DEFAULT '0',
                  `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none',
                  `score` float(10,1) NOT NULL DEFAULT '0.0',
                  `answer` text,
                  `teacherSay` text,
                  `pId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '复制试卷题目Id',
                  `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
                  `migrateItemResultId` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `testPaperResultId` (`resultId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }


        $nextPage = $this->insertTestpaperItemResult($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }

        if (!$this->isIndexExist('testpaper_item_result_v8', 'resultId_type')) {
            $this->getConnection()->exec("
                ALTER TABLE testpaper_item_result_v8 ADD INDEX resultId_type (`resultId`,`type`);
            ");
        }

        if (!$this->isIndexExist('testpaper_item_result_v8', 'testId_type')) {
            $this->getConnection()->exec("
              alter table testpaper_item_result_v8 add index testId_type (testId, type);
            ");
        }

        if (!$this->isIndexExist('testpaper_item_result_v8', 'index_reusltId_type')) {
            $this->getConnection()->exec("ALTER  TABLE  testpaper_item_result_v8  ADD  INDEX  index_reusltId_type (`resultId`,`type`) ; ");
        }
    }

    private function insertTestpaperItemResult($page)
    {
        $countSql = 'SELECT count(id) FROM `testpaper_item_result`';
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count == 0) {
            return;
        }

        $start = $this->getStart($page);
        $sql = "INSERT INTO testpaper_item_result_v8 (
            id,
            itemId,
            testId,
            resultId,
            userId,
            questionId,
            status,
            score,
            answer,
            teacherSay,
            pId,
            migrateItemResultId,
            type
        ) SELECT
            id,
            itemId,
            testId,
            testPaperResultId,
            userId,
            questionId,
            status,
            score,
            answer,
            teacherSay,
            pId,
            id,
            'testpaper'
            FROM testpaper_item_result order by id limit {$start}, {$this->perPageCount};";
        $this->getConnection()->exec($sql);

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;
    }
}
