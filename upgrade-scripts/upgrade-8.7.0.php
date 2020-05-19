<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    const SETTING_KEY = 'upgrade870';

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
            $dir = realpath($this->biz['kernel.root_dir'].'/../web/install');
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
            'createFunctionConverQuestionAnswer',
            'updateQuestionBankItemBankId',
            'processBizItemBank',
            'processBizItemCategory',
            'processBizItem', 
            'processBizQuestion',
            'processBizItemAttachment',
            'processBizAssessmentAndSectionAndSectionItem',
            'processQuestionMarkerResultAnswerStep1', //处理选择题
            'processQuestionMarkerResultAnswerStep2', //处理判断题
            'processBizQuestionFavorite',
            'processActivityMediaId',
            'processActivityTestpaper',
            'processActivityHomework',
            'processActivityExercise',
            'processBizAnswerScene',
            'processBizAnswerRecord',
            'processBizAnswerReport',
            'processUserFace',
            'processBizFaceinCheatRecord',
            'processBizAnswerQuestionReport',
            'processBizAnswerSceneQuestionReport',
            'deleteFunctionConverQuestionAnswer',
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

    public function processBizItemAttachment($page)
    {
        if (!$this->isTableExist('biz_item_attachment')) {
            $sql = "
                CREATE TABLE `biz_item_attachment` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `global_id` varchar(32) NOT NULL DEFAULT '' COMMENT '云文件ID',
                    `hash_id` varchar(128) NOT NULL DEFAULT '' COMMENT '文件的HashID',
                    `target_id` int(10) NOT NULL DEFAULT '0' COMMENT '对象id',
                    `target_type` varchar(32) NOT NULL DEFAULT '' COMMENT '对象类型', 
                    `module` varchar(32) NOT NULL DEFAULT '' COMMENT '附件所属题目模块',
                    `file_name` varchar(1024) NOT NULL DEFAULT '' COMMENT '附件名',
                    `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
                    `size` int(10) NOT NULL DEFAULT '0' COMMENT '文件大小',
                    `status` varchar(32) NOT NULL DEFAULT '' COMMENT '上传状态',
                    `file_type` varchar(32) NOT NULL DEFAULT '' COMMENT '文件类型',
                    `created_user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户Id',
                    `convert_status` varchar(32) NOT NULL DEFAULT 'none' COMMENT '转码状态',
                    `audio_convert_status` varchar(32) NOT NULL DEFAULT 'none' COMMENT '转音频状态',
                    `mp4_convert_status` varchar(32) NOT NULL DEFAULT 'none' COMMENT '转mp4状态',
                    `updated_time` int(10) NOT NULL DEFAULT '0',
                    `created_time` int(10) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `target_id` (`target_id`),
                    KEY `target_type` (`target_type`),
                    KEY `global_id` (`global_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题目附件表';
            ";
            $this->getConnection()->exec($sql);
        }

        $startData = $this->startPage(__FUNCTION__, "SELECT count(id) AS num FROM upload_files WHERE useType IN ('question.stem', 'question.analysis', 'question.answer');", 200);
        if (empty($startData)) {
            return 1;
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);

        $start = $startData['start'];
        $limit = $startData['limit'];
        if (1 == $startData['page']) {
            $this->getConnection()->exec("
                DELETE FROM `biz_item_attachment`;
            ");
        }

        $files = $this->getConnection()->fetchAll("
            SELECT a.*, b.targetId as tid FROM `upload_files` a 
            LEFT JOIN `file_used` b ON a.id = b.fileId 
            WHERE a.useType IN ('question.stem', 'question.analysis', 'question.answer') 
            LIMIT {$start}, {$limit};
        ", array());
        $filesGroup = ArrayToolkit::group($files, 'useType');
        $questionFiles = array_merge(
            empty($filesGroup['question.stem']) ? array() : $filesGroup['question.stem'],
            empty($filesGroup['question.analysis']) ? array() : $filesGroup['question.analysis']
        );
        $questions = ArrayToolkit::index(
            $this->getQuestionDao()->findQuestionsByIds(ArrayToolkit::column($questionFiles, 'tid')),
            'id'
        );

        $insetFiles = array();
        foreach ($files as $file) {
            if (empty($questions[$file['tid']])) {
                continue;
            }
            $targetId = '';
            switch ($file['useType']) {
                case 'question.stem':
                    $question = $questions[$file['tid']];
                    $targetType = 'material' == $question['type'] ? 'item' : 'question';
                    $targetId = $file['tid'];
                    $module = 'material' == $question['type'] ? 'material' : 'stem';
                    break;
                
                case 'question.analysis':
                    $question = $questions[$file['tid']];
                    $targetType = 'material' == $question['type'] ? 'item' : 'question';
                    $targetId = $file['tid'];
                    $module = 'analysis';
                    break;
                
                case 'question.answer':
                    $targetType = 'answer';
                    $targetId = $file['tid'];
                    $module = 'answer';
                    break;
                default:
                    continue;
                    break;
            }
            $insetFiles[] = array(
                'global_id' => $file['globalId'],
                'hash_id' => $file['hashId'],
                'target_id' => $targetId,
                'target_type' => $targetType,
                'module' => $module,
                'file_name' => $file['filename'],
                'ext' => $file['ext'],
                'size' => $file['fileSize'],
                'status' => $file['status'] == 'ok' ? 'finish' : $file['status'],
                'file_type' => $file['type'],
                'created_user_id' => $file['createdUserId'],
                'convert_status' => $file['convertStatus'],
                'audio_convert_status' => $file['audioConvertStatus'],
                'mp4_convert_status' => $file['mp4ConvertStatus'],
                'updated_time' => $file['updatedTime'],
                'created_time' => $file['createdTime'],
            );
        }
        $this->getAttachmentDao()->batchCreate($insetFiles);

        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    public function processUserFace($page)
    {
        $this->logger('info', __FUNCTION__);
        if (!$this->isTableExist('user_face')) {
            $sql = "
                CREATE TABLE IF NOT EXISTS `user_face` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
                    `picture` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '文件路径',
                    `capture_code` varchar (20) NOT NULL DEFAULT '' COMMENT '采集头像时链接码',
                    `created_time` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
                    `updated_time` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='云监考头像采集';
            ";
            $this->getConnection()->exec($sql);
        }

        if ($this->isTableExist('plugin_facein_user_face')) {
            $this->getConnection()->exec("
                DELETE FROM `user_face`;
            ");
            $this->getConnection()->exec("
                INSERT INTO `user_face` (id, user_id, picture, capture_code, created_time, updated_time)
                SELECT
                    id, user_id, picture, capture_code, created_time, updated_time
                FROM `plugin_facein_user_face`;
            ");
        }

        return 1;
    }

    public function processBizFaceinCheatRecord($page)
    {
        if (!$this->isTableExist('biz_facein_cheat_record')) {
            $sql = "
                CREATE TABLE IF NOT EXISTS `biz_facein_cheat_record` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
                    `answer_scene_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '场次id',
                    `answer_record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题记录id',
                    `status` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '作弊状态',
                    `level` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '作弊等级',
                    `duration` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '',
                    `behavior` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '作弊行为',
                    `picture_path` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '文件路径',
                    `created_time` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
                    PRIMARY KEY (`id`),
                    KEY `answer_scene_id` (`answer_scene_id`),
                    KEY `answer_record_id` (`answer_record_id`),
                    KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='云监考作弊记录';
            ";
            $this->getConnection()->exec($sql);
        }

        if ($this->isTableExist('plugin_facein_testpaper_result')) {
            $startData = $this->startPage(__FUNCTION__, "SELECT count(id) AS num FROM plugin_facein_testpaper_result;", 50000);
            if (empty($startData)) {
                return 1;
            }
            $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);

            $start = $startData['start'];
            $limit = $startData['limit'];
            if (1 == $startData['page']) {
                $this->getConnection()->exec("
                    DELETE FROM `biz_facein_cheat_record`;
                ");
            }
            
            $this->getConnection()->exec("
                INSERT INTO `biz_facein_cheat_record` (id, user_id, answer_scene_id, answer_record_id, status, level, duration, behavior, picture_path, created_time)
                SELECT
                    a.id, 
                    a.user_id, 
                    b.answer_scene_id, 
                    a.testpaper_result_id, 
                    a.status, 
                    a.level, 
                    a.duration, 
                    a.behavior, 
                    a.picture, 
                    a.created_time
                FROM `plugin_facein_testpaper_result` a LEFT JOIN `biz_answer_record` b ON a.testpaper_result_id = b.id LIMIT {$start}, {$limit};
            ");

            $endData = $this->endPage(__FUNCTION__);
            if (empty($endData)) {
                return 1;
            } else {
                return $endData['page'];
            }
        } else {
            return 1;
        }
    }

    public function processBizAnswerSceneQuestionReport($page)
    {
        $this->logger('info', __FUNCTION__);
        if (!$this->isTableExist('biz_answer_scene_question_report')) {
            $sql = "
                CREATE TABLE `biz_answer_scene_question_report` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `answer_scene_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '场次id',
                    `question_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题id',
                    `item_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目id',
                    `right_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答对人数',
                    `wrong_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答错人数',
                    `no_answer_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未作答人数',
                    `part_right_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部分正确人数',
                    `response_points_report` text COMMENT '输入点报告',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `answer_scene_id` (`answer_scene_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场次报告';
            ";
            $this->getConnection()->exec($sql);
        }
        return 1;
    }

    public function deleteFunctionConverQuestionAnswer($page)
    {
        $this->logger('info', __FUNCTION__);
        $sql = "
            drop function if exists conver_question_answer;
        ";
        $this->getConnection()->exec($sql);
        return 1;
    }

    public function createFunctionConverQuestionAnswer($page)
    {
        $this->logger('info', __FUNCTION__);
        $sql = "
            drop function if exists conver_question_answer;
            CREATE FUNCTION conver_question_answer(answer_mode VARCHAR(100), answer text) RETURNS text
            begin
                if answer_mode = 'uncertain_choice' or answer_mode = 'single_choice' or answer_mode = 'choice' then 
                    set answer = replace(answer, '0\"', 'A\"');
                    set answer = replace(answer, '1', 'B'); 
                    set answer = replace(answer, '2', 'C'); 
                    set answer = replace(answer, '3', 'D'); 
                    set answer = replace(answer, '4', 'E');
                    set answer = replace(answer, '5', 'F');
                    set answer = replace(answer, '6', 'G');
                    set answer = replace(answer, '7', 'H');
                    set answer = replace(answer, '8', 'I');
                    set answer = replace(answer, '9', 'J');
                    set answer = replace(answer, '10\"', 'K\"');
                elseif answer_mode = 'determine' or answer_mode = 'true_false' then
                    set answer = replace(answer, '0', 'T');
                    set answer = replace(answer, '1', 'F'); 
                else
                    set answer = answer;
                end if;
                return answer;
            end;
        ";
        $this->getConnection()->exec($sql);
        return 1;
    }

    public function processBizAnswerQuestionReport($page)
    {
        $startData = $this->startPage(__FUNCTION__, "SELECT count(id) AS num FROM testpaper_item_result_v8 where type <> 'exercise';", 80000);
       
        if (1 == $startData['page'] && $this->isTableExist('biz_answer_question_report')) {
            $this->getConnection()->exec("
                DROP TABLE `biz_answer_question_report`;
            ");
        }

        if (!$this->isTableExist('biz_answer_question_report')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_answer_question_report` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `identify` varchar(255) NOT NULL COMMENT '唯一标识，(answer_record_id)_(question_id)',
                    `answer_record_id` int(10) unsigned NOT NULL COMMENT '答题记录id',
                    `assessment_id` int(10) unsigned NOT NULL COMMENT '试卷id',
                    `section_id` int(10) unsigned NOT NULL COMMENT '试卷模块id',
                    `item_id` int(10) unsigned NOT NULL COMMENT '题目id',
                    `question_id` int(10) unsigned NOT NULL COMMENT '问题id',
                    `score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '得分',
                    `total_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '满分数',
                    `response` text,
                    `status` enum('reviewing','right','wrong','no_answer', 'part_right') NOT NULL DEFAULT 'reviewing' COMMENT '状态',
                    `comment` text COMMENT '评语',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                    PRIMARY KEY (`id`),
                    KEY `answer_record_id` (`answer_record_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题目问题报告表';
            ");
        }

        if (empty($startData)) {
            return 1;
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);

        $start = $startData['start'];
        $limit = $startData['limit'];
        $sql = "
            INSERT INTO `biz_answer_question_report` (id, identify, answer_record_id, assessment_id, section_id, item_id, question_id, score, total_score, response, status, comment)
            SELECT
                a.id,
                concat_ws('_', a.resultId, a.questionId),
                a.resultId,
                a.testId,
                '0',
                if(b.item_id is null, 0, b.item_id),
                a.questionId,
                a.score,
                '0',
                conver_question_answer(b.answer_mode,a.answer),
                CASE a.status
                    WHEN 'noAnswer' THEN 'no_answer'
                    WHEN 'none' THEN 'reviewing'
                    WHEN 'partRight' THEN 'part_right'
                    WHEN 'right' THEN 'right'
                    WHEN 'wrong' THEN 'wrong'
                    ELSE 'reviewing'
                END,
                a.teacherSay
            FROM `testpaper_item_result_v8` a 
            LEFT JOIN `biz_question` b ON a.questionId = b.id
            WHERE a.`type` <> 'exercise' LIMIT {$start}, {$limit};
        ";
        
        $this->getConnection()->exec($sql);

        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    public function processBizAnswerReport($page)
    {
        $startData = $this->startPage(__FUNCTION__, "SELECT COUNT(id) AS num FROM testpaper_result_v8 WHERE `type` <> 'exercise';", 50000);

        if (1 == $startData['page'] && $this->isTableExist('biz_answer_report')) {
            $this->getConnection()->exec("
                DROP TABLE `biz_answer_report`;
            ");
        }

        if (!$this->isTableExist('biz_answer_report')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_answer_report` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
                    `assessment_id` int(10) unsigned NOT NULL COMMENT '试卷id',
                    `answer_record_id` int(10) unsigned NOT NULL COMMENT '答题记录id',
                    `answer_scene_id` int(10) unsigned NOT NULL COMMENT '场次id',
                    `total_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '总分',
                    `score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '总得分',
                    `right_rate` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '正确率',
                    `right_question_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答对问题数',
                    `objective_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '客观题得分',
                    `subjective_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '主观题得分',
                    `grade` enum('none', 'excellent','good','passed','unpassed') NOT NULL DEFAULT 'none' COMMENT '等级',
                    `comment` text COMMENT '评语',
                    `review_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '批阅时间',
                    `review_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '批阅人id',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `answer_record_id` (`answer_record_id`),
                    KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='答题报告';
            ");
        }

        if (empty($startData)) {
            return 1;
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);

        $start = $startData['start'];
        $limit = $startData['limit'];
        $sql = "
            INSERT INTO `biz_answer_report` (id, user_id, assessment_id, answer_record_id, answer_scene_id, total_score, score, right_rate, right_question_count, objective_score, subjective_score, grade, comment, review_time, review_user_id, created_time, updated_time) 
            SELECT 
                a.id,
                a.userId,
                a.testId,
                a.id,
                a.lessonId,
                if(b.score is null, 0, b.score),
                a.score,
                if(b.itemCount = 0, 0, if(b.score is null, 0, a.rightItemCount / b.itemCount)),
                a.rightItemCount,
                a.objectiveScore,
                a.subjectiveScore,
                a.passedStatus,
                a.teacherSay,
                a.checkedTime,
                a.checkTeacherId,
                a.endTime,
                a.updateTime
            FROM `testpaper_result_v8` a LEFT JOIN `testpaper_v8` b ON a.testId = b.id
            WHERE a.`type` <> 'exercise' LIMIT {$start}, {$limit};
        ";
        
        $this->getConnection()->exec($sql);

        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    public function processBizAnswerRecord($page)
    {
        $startData = $this->startPage(__FUNCTION__, "SELECT COUNT(id) AS num FROM testpaper_result_v8 WHERE `type` <> 'exercise';", 50000);
        if (1 == $startData['page'] && $this->isTableExist('biz_answer_record')) {
            $this->getConnection()->exec("
                DROP TABLE `biz_answer_record`;
            ");
        }

        if (!$this->isTableExist('biz_answer_record')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_answer_record` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `answer_scene_id` int(10) unsigned NOT NULL COMMENT '场次id',
                    `assessment_id` int(10) unsigned NOT NULL COMMENT '试卷id',
                    `answer_report_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题报告id',
                    `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题者id',
                    `begin_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始答题时间',
                    `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束答题时间',
                    `used_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题时长-秒',
                    `status` enum('doing','paused','reviewing','finished') NOT NULL DEFAULT 'doing' COMMENT '答题状态',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                    PRIMARY KEY (`id`),
                    KEY `answer_scene_id` (`answer_scene_id`),
                    KEY `user_id` (`user_id`),
                    KEY `answer_scene_id_status` (`answer_scene_id`,`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='答题记录表';
            ");
        }
        
        if (empty($startData)) {
            return 1;
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);

        $start = $startData['start'];
        $limit = $startData['limit'];
        $sql = "
            INSERT INTO biz_answer_record (id, answer_scene_id, assessment_id, answer_report_id, user_id, begin_time, end_time, used_time, status, created_time, updated_time)
            SELECT  id,
                    lessonId,
                    testId,
                    id,
                    userId,
                    beginTime,
                    endTime,
                    usedTime,
                    `status`,
                    beginTime,
                    updateTime
            FROM `testpaper_result_v8`
            WHERE `type` <> 'exercise' LIMIT {$start}, {$limit}
        ";
        
        $this->getConnection()->exec($sql);
        
        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    public function processBizAnswerScene($page)
    {
        if (!$this->isTableExist('biz_answer_scene')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_answer_scene` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL COMMENT '场次名称',
                    `limited_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题限制时长(分钟) 0表示不限制',
                    `do_times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可作答次数 0表示不限制',
                    `redo_interval` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题间隔时长(分钟)',
                    `need_score` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要计算分值 1表示需要',
                    `manual_marking` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否支持手动批阅 1表示支持',
                    `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始答题时间 0表示不限制，可作答次数为1时可设置',
                    `pass_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '合格分',
                    `enable_facein` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否开启云监考',
                    `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者id',
                    `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新者id',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='答题场次表';
            ");
        }

        $startData = $this->startPage(__FUNCTION__, "SELECT COUNT(id) AS num FROM activity WHERE mediaType IN ('exercise', 'testpaper', 'homework');", 2000);
        if (empty($startData)) {
            return 1;
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);
        if (1 == $startData['page']) {
            $this->getConnection()->exec("
                DELETE FROM biz_answer_scene;
            ");
        }
        $answerScenes = array();

        $start = $startData['start'];
        $limit = $startData['limit'];
        $sql = "
            SELECT id, title, startTime, mediaId, mediaType, finishData FROM activity WHERE mediaType IN ('exercise', 'testpaper', 'homework') LIMIT {$start}, {$limit};    
        ";
        $activities = $this->getConnection()->fetchAll($sql, array());
        $activitiesGroup = ArrayToolkit::group($activities, 'mediaType');
        
        if (isset($activitiesGroup['homework'])) {
            foreach ($activitiesGroup['homework'] as $activity) {
                $answerScenes[] = array(
                    'id' => $activity['id'],
                    'name' => $activity['title'],
                    'limited_time' => 0,
                    'do_times' => 0,
                    'redo_interval' => 0,
                    'need_score' => 0,
                    'manual_marking' => 1,
                    'start_time' => 0,
                    'pass_score' => 0,
                    'enable_facein' => 0,
                );
            }
        }

        if (isset($activitiesGroup['exercise'])) {
            foreach ($activitiesGroup['exercise'] as $activity) {
                $answerScenes[] = array(
                    'id' => $activity['id'],
                    'name' => $activity['title'],
                    'limited_time' => 0,
                    'do_times' => 0,
                    'redo_interval' => 0,
                    'need_score' => 0,
                    'manual_marking' => 0,
                    'start_time' => 0,
                    'pass_score' => 0,
                    'enable_facein' => 0,
                );
            }
        }

        if (isset($activitiesGroup['testpaper'])) {
            $testpaperActivities = ArrayToolkit::index(
                $this->getTestpaperActivityDao()->findByIds(ArrayToolkit::column($activitiesGroup['testpaper'], 'mediaId')),
                'id'
            );
            $faceinCourseTasks = array();
            if ($this->isTableExist('plugin_facein_course_task')) {
                $activityIds = implode(',', ArrayToolkit::column($activitiesGroup['testpaper'], 'id'));
                if (!empty($activityIds)) {
                    $sql = "
                        SELECT b.activityId, a.enable_facein FROM plugin_facein_course_task a LEFT JOIN course_task b ON a.id = b.id WHERE b.activityId IN ({$activityIds});
                    ";
                    $faceinCourseTasks = ArrayToolkit::index(
                        $this->getConnection()->fetchAll($sql, array()), 
                        'activityId'
                    );
                }
            }
            foreach ($activitiesGroup['testpaper'] as $activity) {
                $assessment = $this->getAssessmentDao()->get($testpaperActivities[$activity['mediaId']]['mediaId']);
                $passScore = 0;
                if ($assessment) {
                    $passScore = intval($assessment['total_score'] * $activity['finishData']);
                }
                $answerScenes[] = array(
                    'id' => $activity['id'],
                    'name' => $activity['title'],
                    'limited_time' => $testpaperActivities[$activity['mediaId']]['limitedTime'],
                    'do_times' => $testpaperActivities[$activity['mediaId']]['doTimes'],
                    'redo_interval' => $testpaperActivities[$activity['mediaId']]['redoInterval'] * 60,
                    'need_score' => 1,
                    'manual_marking' => 1,
                    'start_time' => $activity['startTime'],
                    'pass_score' => $passScore,
                    'enable_facein' => empty($faceinCourseTasks[$activity['id']]) ? 0 : $faceinCourseTasks[$activity['id']]['enable_facein'],
                );
            }
        }
       
        $this->getAnswerSceneDao()->batchCreate($answerScenes);

        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    public function processActivityMediaId($page)
    {
        $this->logger('info', __FUNCTION__);

        if (!$this->isFieldExist('activity', 'mediaIdBackup')) {
            $this->getConnection()->exec("
                ALTER TABLE `activity` ADD `mediaIdBackup` INT(10) NOT NULL DEFAULT '0' COMMENT '公共题库升级备份用' AFTER `mediaId`;
                UPDATE `activity` SET `mediaIdBackup` = `mediaId` WHERE `mediaType` IN('exercise', 'homework');
            ");
        }

        $this->getConnection()->exec("
            UPDATE `activity` SET `mediaId` = `id` WHERE `mediaType` IN('exercise', 'homework');
        ");

        return 1;
    }
    
    public function processActivityHomework($page)
    {
        $this->logger('info', __FUNCTION__);

        if (!$this->isTableExist('activity_homework')) {
            $this->getConnection()->exec("
                CREATE TABLE `activity_homework` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `answerSceneId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '场次ID',
                    `assessmentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷id',
                    `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                    `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `answerSceneId` (`answerSceneId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='作业活动表';
            ");
        }

        $this->getConnection()->exec("
            DELETE FROM activity_homework;
        ");

        $this->getConnection()->exec("
            INSERT INTO activity_homework (id, assessmentId, answerSceneId, createdTime, updatedTime)
            SELECT 
                a.id, if(c.locked = 1, b.copyId, if(b.id is null, 0, b.id)) AS assessmentId, a.id AS answerSceneId, a.createdTime, a.updatedTime
                FROM activity a 
                LEFT JOIN testpaper_v8 b ON a.mediaIdBackup = b.id 
                LEFT JOIN course_set_v8 c ON b.courseSetId = c.id
            WHERE a.mediaType = 'homework';
        ");

        return 1;
    }

    public function processActivityTestpaper($page)
    {
        $this->logger('info', __FUNCTION__);

        if (!$this->isFieldExist('activity_testpaper', 'answerSceneId')) {
            $this->getConnection()->exec("
                ALTER TABLE `activity_testpaper` ADD `answerSceneId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '答题引擎场次id' AFTER `testMode`;
                ALTER TABLE `activity_testpaper` ADD INDEX(`answerSceneId`);
            ");
        }

        if (!$this->isFieldExist('activity_testpaper', 'mediaIdBackup')) {
            $this->getConnection()->exec("
                ALTER TABLE `activity_testpaper` ADD `mediaIdBackup` INT(10) NOT NULL DEFAULT '0' COMMENT '公共题库升级备份用' AFTER `mediaId`;
                UPDATE `activity_testpaper` SET `mediaIdBackup` = `mediaId`;
            ");
        }
        
        $this->getConnection()->exec("
            UPDATE activity_testpaper
                LEFT JOIN activity ON activity.mediaId = activity_testpaper.id
                LEFT JOIN testpaper_v8 ON activity_testpaper.mediaIdBackup = testpaper_v8.id
                LEFT JOIN course_set_v8 ON testpaper_v8.courseSetId = course_set_v8.id
                SET activity_testpaper.answerSceneId = activity.id, activity_testpaper.mediaId = if(course_set_v8.locked = 1, testpaper_v8.copyId, if(testpaper_v8.id is null, 0, testpaper_v8.id ))
            WHERE activity.mediaType = 'testpaper';
        ");

        return 1;
    }
    
    public function processActivityExercise($page)
    {
        if (!$this->isTableExist('activity_exercise')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `activity_exercise` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `answerSceneId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '场次ID',
                    `drawCondition` TEXT COMMENT '抽题条件',
                    `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
                    `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `answerSceneId` (`answerSceneId`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='练习活动表';
            ");
        }

        $startData = $this->startPage(__FUNCTION__, "SELECT COUNT(id) AS num FROM activity WHERE mediaType = 'exercise';", 1000);
        if (empty($startData)) {
            return 1;
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);
        if (1 == $startData['page']) {
            $this->getConnection()->exec("
                DELETE FROM activity_exercise;
            ");
        }

        $start = $startData['start'];
        $limit = $startData['limit'];
        $sql = "
            SELECT 
                a.id, a.createdTime, b.updatedTime, if(c.locked = 1, d.metas, b.metas) as metas, b.itemCount
                FROM activity a 
                LEFT JOIN testpaper_v8 b ON a.mediaIdBackup = b.id 
                LEFT JOIN course_set_v8 c ON b.courseSetId = c.id
                LEFT JOIN testpaper_v8 d ON b.copyId = d.id
            WHERE a.mediaType = 'exercise' LIMIT {$start}, {$limit};    
        ";
        $activities = $this->getConnection()->fetchAll($sql, array());
        
        $exerciseActivities = array();
        foreach ($activities as $activity) {
            $drawCondition = $this->converDrawCondition($activity);
            if (empty($drawCondition)) {
                continue;
            }
            $exerciseActivities[] = array(
                'id' => $activity['id'],
                'answerSceneId' => $activity['id'],
                'drawCondition' => $drawCondition,
                'createdTime' => $activity['createdTime'],
                'updatedTime' => $activity['updatedTime'],
            );
        }
        $this->getExerciseActivityDao()->batchCreate($exerciseActivities);

        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    protected function converDrawCondition($activity)
    {
        $metas = json_decode($activity['metas'], true);
        $drawCondition = array();
        if (isset($metas['range']['lessonId']) || isset($metas['range']['courseId']) || empty($metas['range']) || !is_array($metas['range'])) {
            return $drawCondition;
        }
        
        $drawCondition['range'] = array(
            'question_bank_id' => $metas['range']['bankId'],
            'bank_id' => $metas['range']['bankId'],
            'category_ids' => explode(',', $metas['range']['categoryIds']),
            'difficulty' => empty($metas['difficulty']) ? '' : $metas['difficulty'],
        );
        
        $drawCondition['section'] = array(
            'conditions' => array(
                'item_types' => $metas['questionTypes'],
            ),
            'item_count' => $activity['itemCount'],
            'name' => '练习题目',
        );

        return $drawCondition;
    }

    public function processBizQuestionFavorite($page)
    {
        if (!$this->isTableExist('biz_question_favorite')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_question_favorite` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `target_type` varchar(32) NOT NULL DEFAULT '' COMMENT '收藏来源',
                    `target_id` int(10) NOT NULL DEFAULT '0' COMMENT '收藏来源id',
                    `question_id` int(10) NOT NULL DEFAULT '0' COMMENT '问题id',
                    `item_id` int(10) NOT NULL DEFAULT '0' COMMENT '题目id',
                    `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户Id',
                    `created_time` int(10) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `target_type_and_target_id` (`target_type`,`target_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题目收藏表';
            ");
        }

        $startData = $this->startPage(__FUNCTION__, "SELECT COUNT(id) AS num FROM question_favorite;", 30000);
        if (empty($startData)) {
            return 1;
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);
        if (1 == $startData['page']) {
            $this->getConnection()->exec("
                DELETE FROM `biz_question_favorite`;
            ");
        }

        $start = $startData['start'];
        $limit = $startData['limit'];
        $sql = "
            INSERT INTO biz_question_favorite (id, target_type, target_id, question_id, item_id, user_id, created_time)
            SELECT a.id, 'assessment', a.targetId, a.questionId, b.item_id, a.userId, a.createdTime FROM question_favorite a INNER JOIN biz_question AS b ON a.questionId = b.id LIMIT {$start}, {$limit}
        ";

        $this->getConnection()->exec($sql);

        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    public function processQuestionMarkerResultAnswerStep1($page)
    {
        $startData = $this->startPage(__FUNCTION__, "SELECT COUNT(id) AS num FROM question_marker_result;", 50000);
        if (empty($startData)) {
            return 1;
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);

        $start = $startData['start'];
        $limit = $startData['limit'];
        $sql = "
            UPDATE `question_marker_result` SET  
                answer = replace(answer, '0\"', 'A\"'),
                answer = replace(answer, '1', 'B'), 
                answer = replace(answer, '2', 'C'), 
                answer = replace(answer, '3', 'D'), 
                answer = replace(answer, '4', 'E'),
                answer = replace(answer, '5', 'F'),
                answer = replace(answer, '6', 'G'),
                answer = replace(answer, '7', 'H'),
                answer = replace(answer, '8', 'I'),
                answer = replace(answer, '9', 'J'),
                answer = replace(answer, '10\"', 'K\"')
            WHERE questionMarkerId IN (
                SELECT id FROM (
                    SELECT id FROM question_marker WHERE type IN ('uncertain_choice', 'single_choice', 'choice') LIMIT {$start}, {$limit}
                ) as t
            );
        ";
        $this->getConnection()->exec($sql);

        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    public function processQuestionMarkerResultAnswerStep2($page)
    {
        $startData = $this->startPage(__FUNCTION__, "SELECT COUNT(id) AS num FROM question_marker_result;", 50000);
        if (empty($startData)) {
            return 1;
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);

        $start = $startData['start'];
        $limit = $startData['limit'];
        $sql = "
            UPDATE `question_marker_result` SET  
                answer = replace(answer, '0', 'T'),
                answer = replace(answer, '1', 'F')
            WHERE questionMarkerId IN (
                SELECT t.id FROM (
                    SELECT id FROM question_marker WHERE type = 'determine' LIMIT {$start}, {$limit}
                ) as t
            );
        ";
        $this->getConnection()->exec($sql);

        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    public function processBizAssessmentAndSectionAndSectionItem($page)
    {
        if (!$this->isTableExist('biz_assessment')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_assessment` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `bank_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属题库id',
                    `displayable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示 1表示显示',
                    `name` varchar(255) NOT NULL COMMENT '试卷名称',
                    `description` text COMMENT '试卷说明',
                    `total_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '总分',
                    `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '试卷状态：draft,open,closed',
                    `item_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
                    `question_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题数量',
                    `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者ID',
                    `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新者ID',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `bank_id` (`bank_id`),
                    KEY `status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷表';
            ");
        }

        if (!$this->isTableExist('biz_assessment_section')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_assessment_section` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `assessment_id` int(10) unsigned NOT NULL COMMENT '试卷ID',
                    `name` varchar(255) NOT NULL COMMENT '名称',
                    `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模块顺序',
                    `description` text COMMENT '模块说明',
                    `item_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
                    `total_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '总分',
                    `question_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题数量',
                    `score_rule` varchar(512) NOT NULL DEFAULT '' COMMENT '得分规则',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `assessment_id` (`assessment_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷模块表';
            ");
        }

        if (!$this->isTableExist('biz_assessment_section_item')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_assessment_section_item` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `assessment_id` int(10) unsigned NOT NULL COMMENT '试卷ID',
                    `item_id` int(10) unsigned NOT NULL COMMENT '题目ID',
                    `section_id` int(10) unsigned NOT NULL COMMENT '模块ID',
                    `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
                    `score` float(10,1) NOT NULL COMMENT '题目分数',
                    `question_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题数量',
                    `question_scores` text COMMENT '问题分数',
                    `score_rule` text COMMENT '得分规则(包括题项)',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `section_id` (`section_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷题目表';
            ");
        }

        $startData = $this->startPage(__FUNCTION__, "SELECT COUNT(*) AS num FROM testpaper_v8 WHERE `type` <> 'exercise';", 150);
        if (empty($startData)) {
            return 1;
        }
        if (1 == $startData['page']) {
            $this->getConnection()->exec("
                DELETE FROM `biz_assessment`;
                DELETE FROM `biz_assessment_section`;
                DELETE FROM `biz_assessment_section_item`;
            ");
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);
        
        $testpapers = $this->getTestpaperDao()->search(array('types' => array('testpaper', 'homework')), array(), $startData['start'], $startData['limit']);
        $testpaperItems = ArrayToolkit::group(
            $this->getTestpaperItemDao()->findItemsByTestIds(ArrayToolkit::column($testpapers, 'id')), 'testId'
        );
        
        $assessments = array();
        $assessmentSections = array();
        $assessmentSectionItems = array();
        foreach ($testpapers as $testpaper) {
            $questionCount = 0;
            $itemCount = 0;
            $items = empty($testpaperItems[$testpaper['id']]) ? array() : $testpaperItems[$testpaper['id']];
            if (count($items) > 0) {
                if ('homework' == $testpaper['type']) {
                   $sectionAndItems = $this->getSectionAndItems($items, $testpaper);
                   $questionCount += $sectionAndItems['section']['question_count'];
                   $itemCount += $sectionAndItems['section']['item_count'];
                   $sectionAndItems['section']['name'] = '作业题目';
                   $assessmentSections[] = $sectionAndItems['section'];
                   $assessmentSectionItems = array_merge($assessmentSectionItems, $sectionAndItems['items']);
                } else {
                    $dict = array(
                        'single_choice' => '单选题',
                        'choice' => '多选题',
                        'essay' => '问答题',
                        'uncertain_choice' => '不定向选择题',
                        'determine' => '判断题',
                        'fill' => '填空题',
                        'material' => '材料题',
                    );
                    $sections = ArrayToolkit::group($items, 'questionType');
                    foreach ($sections as $questionType => $sectionItems) {
                        if ($questionType == 'material') {
                            //把子题加回去
                            foreach ($items as $key => $item) {
                                if ($item['parentId'] > 0) {
                                    $sectionItems[] = $item;
                                }
                            }
                        } else {
                            //把子题去掉
                            foreach ($sectionItems as $key => $item) {
                                if ($item['parentId'] > 0) {
                                    unset($sectionItems[$key]);
                                }
                            }
                        }
                        if (empty($sectionItems)) {
                            continue;
                        }
                        $sectionAndItems = $this->getSectionAndItems(array_values($sectionItems), $testpaper);
                        $questionCount += $sectionAndItems['section']['question_count'];
                        $itemCount += $sectionAndItems['section']['item_count'];
                        $sectionAndItems['section']['name'] = empty($dict[$questionType]) ? '其他' : $dict[$questionType];
                        $assessmentSections[] = $sectionAndItems['section'];
                        $assessmentSectionItems = array_merge($assessmentSectionItems, $sectionAndItems['items']);
                    }
                }
            }
            
            $assessments[] = array(
                'id' => $testpaper['id'],
                'bank_id' => $testpaper['bankId'],
                'displayable' => $testpaper['type'] == 'testpaper' ? 1 : 0,
                'name' => $testpaper['name'],
                'description' => $testpaper['description'],
                'total_score' => $testpaper['score'],
                'status' => $testpaper['status'],
                'item_count' => $itemCount,
                'question_count' => $questionCount,
                'created_user_id' => $testpaper['createdUserId'],
                'updated_user_id' => $testpaper['updatedUserId'],
                'updated_time' => $testpaper['createdTime'],
                'updated_time' => $testpaper['updatedTime'],
            );

        }
        
        $this->getAssessmentDao()->batchCreate($assessments);
        $this->getAssessmentSectionDao()->batchCreate($assessmentSections);
        $this->getAssessmentSectionItemDao()->batchCreate($assessmentSectionItems);
        
        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }

        return 1;
    }

    protected function getSectionAndItems($items, $testpaper)
    {
        $itemCount = 0;
        $questionCount = 0;
        $assessmentItems = array();
        $sectionItems = array();
        $questions = array();
        foreach ($items as $item) {
            if ('material' != $item['questionType']) {
                if ($item['parentId'] != 0) {
                    $questions[] = $item;
                }
                $questionCount++;
            }
            if ($item['parentId'] == 0) {
                $sectionItems[] = $item;
                $itemCount++;
            }
        }
        $questions = ArrayToolkit::group($questions, 'parentId');
        foreach ($sectionItems as $key => $sectionItem) {
            $subQuestions = empty($questions[$sectionItem['questionId']]) ? array($sectionItem) : $questions[$sectionItem['questionId']];
            $questionScores = array();
            $scoreRule = array();
            foreach ($subQuestions as $subQuestion) {
                $questionScores[] = array(
                    'question_id' => $subQuestion['questionId'],
                    'score' => $subQuestion['score'],
                );
                $rule = array(
                    array('name' => 'all_right', 'score' => $subQuestion['score']),
                    array('name' => 'no_answer', 'score' => 0),
                    array('name' => 'wrong', 'score'=> 0),
                );
                if ($subQuestion['missScore'] > 0) {
                    $rule[] = array('name' => 'part_right', 'score' => $subQuestion['missScore']);
                }
                $scoreRule[] = array(
                    'question_id' => $subQuestion['questionId'],
                    'seq' => $subQuestion['seq'],
                    'rule' => $rule
                );
            }
            $assessmentItems[] = array(
                'id' => $sectionItem['id'],
                'assessment_id' => $testpaper['id'],
                'item_id' => $sectionItem['questionId'],
                'section_id' => $items[0]['id'],
                'seq' => $key + 1,
                'score' => array_sum(ArrayToolkit::column($questionScores, 'score')),
                'question_count' => count($subQuestions),
                'question_scores' => $questionScores,
                'score_rule' => $scoreRule,
            );
        }

        return array(
            'section' => array(
                'id' => $items[0]['id'],
                'assessment_id' => $testpaper['id'],
                'name' => '',
                'seq' => '1',
                'item_count' => $itemCount,
                'question_count' => $questionCount,
                'total_score' => array_sum(ArrayToolkit::column($assessmentItems, 'score')),
            ),
            'items' => $assessmentItems,
        );
    }

    public function updateQuestionBankItemBankId($page)
    {
        $this->logger('info', __FUNCTION__);

        if (!$this->isFieldExist('question_bank', 'itemBankId')) {
            $this->getConnection()->exec("
                ALTER TABLE `question_bank` ADD COLUMN `itemBankId` INT(10) NOT NULL comment '标准题库id';
            ");
        }

        $this->getConnection()->exec("
            UPDATE `question_bank` SET itemBankId = `id`;
        ");

        return 1;
    }

    public function processBizItemBank($page)
    {
        $this->logger('info', __FUNCTION__);

        if (!$this->isTableExist('biz_item_bank')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_item_bank` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(1024) NOT NULL COMMENT '题库名称',
                    `assessment_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷数量',
                    `item_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
                    `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者',
                    `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新更新用户',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题库表';
            ");
        }

        $this->getConnection()->exec("
            DELETE FROM biz_item_bank;
        ");

        $this->getConnection()->exec("
            INSERT INTO biz_item_bank (id, `name`, assessment_num, item_num, created_time, updated_time) 
            SELECT id, `name`, testpaperNum, questionNum, createdTime, updatedTime FROM question_bank;
        ");

        return 1;
    }

    public function processBizItemCategory($page)
    {
        $this->logger('info', __FUNCTION__);

        if (!$this->isTableExist('biz_item_category')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_item_category` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(1024) NOT NULL COMMENT '名称',
                    `weight` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '权重',
                    `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级分类id',
                    `bank_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属题库id',
                    `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者',
                    `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新更新用户',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `bank_id` (`bank_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题目分类表';
            ");
        }

        $startData = $this->startPage(__FUNCTION__, 'SELECT COUNT(*) AS num FROM question_category;', 5000);
        if (empty($startData)) {
            return 1;
        }
        if (1 == $startData['page']) {
            $this->getConnection()->exec("DELETE FROM `biz_item_category`");
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);

        $start = $startData['start'];
        $limit = $startData['limit'];
        $this->getConnection()->exec("
            INSERT INTO biz_item_category (id, `name`, weight, parent_id, bank_id, created_user_id, updated_user_id, created_time, updated_time)
            SELECT id, `name`, weight, parentId, bankId, userId, userId, createdTime, updatedTime FROM question_category LIMIT {$start}, {$limit};
        ");

        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    public function processBizItem($page)
    {
        if (!$this->isTableExist('biz_item')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_item` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目ID',
                    `bank_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属题库id',
                    `type` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类型',
                    `material` text COMMENT '题目材料',
                    `analysis` text COMMENT '题目解析',
                    `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类别',
                    `difficulty` varchar(64) NOT NULL DEFAULT 'normal' COMMENT '难度',
                    `question_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题数量',
                    `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者',
                    `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新更新用户',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                    PRIMARY KEY (`id`),
                    KEY `bank_id` (`bank_id`),
                    KEY `difficulty` (`difficulty`),
                    KEY `category_id` (`category_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题目表';
            ");
        }

        $startData = $this->startPage(__FUNCTION__, 'SELECT COUNT(*) AS num FROM question WHERE parentId = 0', 50000);
        if (empty($startData)) {
            return 1;
        }
        if (1 == $startData['page']) {
            $this->getConnection()->exec("DELETE FROM `biz_item`");
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);
        
        $start = $startData['start'];
        $limit = $startData['limit'];
        $sql = "
            INSERT INTO biz_item (id, bank_id, type, material, analysis
                , category_id, difficulty, question_num, created_user_id, updated_user_id
                , updated_time, created_time)
            SELECT id, bankId, type, stem, analysis
                , categoryId, difficulty, subCount, createdUserId, updatedUserId
                , updatedTime, createdTime
            FROM question
            WHERE parentId = 0 LIMIT {$start}, {$limit};
        ";
        $this->getConnection()->exec($sql);

        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    public function processBizQuestion($page)
    {
        if (!$this->isTableExist('biz_question')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_question` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '问题ID',
                    `item_id` int(10) unsigned NOT NULL COMMENT '题目ID',
                    `stem` text COMMENT '题干',
                    `seq` int(10) unsigned NOT NULL COMMENT '序号',
                    `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
                    `answer_mode` varchar(255) NOT NULL DEFAULT '' COMMENT '作答方式',
                    `response_points` text COMMENT '答题点信息',
                    `answer` text COMMENT '参考答案',
                    `analysis` text COMMENT '问题解析',
                    `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者',
                    `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新更新用户',
                    `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                    `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                    PRIMARY KEY (`id`),
                    KEY `item_id` (`item_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问题表';
            ");
        }

        $startData = $this->startPage(__FUNCTION__, "SELECT COUNT(*) AS num FROM question WHERE `type` <> 'material';", 1000);
        if (empty($startData)) {
            return 1;
        }
        if (1 == $startData['page']) {
            $this->getConnection()->exec("DELETE FROM `biz_question`");
        }
        $this->logger('info', __FUNCTION__.' page:'.$startData['page'].'/'.$startData['pageCount']);

        $questions = $this->getQuestionDao()->search(array('types' => array('choice', 'essay', 'determine', 'fill', 'uncertain_choice', 'single_choice')), array(), $startData['start'], $startData['limit']);
        $bizQuestions = array();
        foreach ($questions as $question) {
            $bizQuestions[] = $this->converBizQuestion($question);
        }
        $this->getBizQuestionDao()->batchCreate($bizQuestions);
        
        $endData = $this->endPage(__FUNCTION__);
        if (empty($endData)) {
            return 1;
        } else {
            return $endData['page'];
        }
    }

    protected function converBizQuestion($question)
    {
        $english = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        switch ($question['type']) {
            case 'choice':
                $answerMode = 'choice';

                $answer = array();
                foreach ($question['answer'] as $questionAnswer) {
                    $answer[] = $english[$questionAnswer];
                }

                $responsePoints = array();
                foreach ($question['metas']['choices'] as $key => $text) {
                    $responsePoints[] = array(
                        'checkbox' => array('val' => $english[$key], 'text' => $text)
                    );
                }
                break;
            
            case 'essay':
                $answerMode = 'rich_text';
                $answer = $question['answer'];
                $responsePoints = array('rich_text' => array());
                break;

            case 'determine':
                $answerMode = 'true_false';
                $answer = $question['answer'][0] == '1' ? array('T') : array('F');
                $responsePoints = array(
                    array('radio' => array('val' => 'T', 'text' => '正确')),
                    array('radio' => array('val' => 'F', 'text' => '错误')),
                );
                break;
            
            case 'fill':
                $answerMode = 'text';
                $answer = array();
                $responsePoints = array();
                $question['stem'] = preg_replace('/\[\[.+?\]\]/', '[[]]', $question['stem']);
                foreach ($question['answer'] as $questionAnswer) {
                    $answer[] = implode($questionAnswer, '|');
                    $responsePoints[] = array('text' => array());
                }
                break;
            
            case 'uncertain_choice':
                $answerMode = 'uncertain_choice';
                
                $answer = array();
                foreach ($question['answer'] as $questionAnswer) {
                    $answer[] = $english[$questionAnswer];
                }

                $responsePoints = array();
                foreach ($question['metas']['choices'] as $key => $text) {
                    $responsePoints[] = array(
                        'checkbox' => array('val' => $english[$key], 'text' => $text)
                    );
                }
                break;

            case 'single_choice':
                $answerMode = 'single_choice';
                
                $answer = array();
                foreach ($question['answer'] as $questionAnswer) {
                    $answer[] = $english[$questionAnswer];
                }

                $responsePoints = array();
                foreach ($question['metas']['choices'] as $key => $text) {
                    $responsePoints[] = array(
                        'radio' => array('val' => $english[$key], 'text' => $text)
                    );
                }

                break;
            
            default:
                $answerMode = '';
                $answer = [];
                $responsePoints = [];
                break;
        }

        $bizQuestion = [
            'id' => $question['id'],
            'item_id' => $question['parentId'] == 0 ? $question['id'] : $question['parentId'],
            'stem' => $question['stem'],
            'seq' => $question['parentId'] == 0 ? 1 : 0,
            'score' => $question['score'],
            'answer_mode' => $answerMode,
            'response_points' => $responsePoints,
            'answer' => $answer,
            'analysis' => $question['analysis'],
            'created_user_id' => $question['createdUserId'],
            'updated_user_id' => $question['updatedUserId'],
            'updated_time' => $question['updatedTime'],
            'created_time' => $question['createdTime'],
        ];

        return $bizQuestion;
    }

    protected function countTable($countSql)
    {
        $result = $this->getConnection()->fetchAssoc($countSql, array());
        return $result['num'];
    }

    protected function startPage($funcName, $countSql, $limit = 50000)
    {
        $upgradeSetting = $this->getSettingService()->get(self::SETTING_KEY, array());
        $this->logger('info', 'startPage:get'.json_encode($upgradeSetting));
        if (empty($upgradeSetting[$funcName])) {
            $pageCount = ceil($this->countTable($countSql) / $limit);
            if (0 == $pageCount) {
                return;
            }
            $startData = array(
                'pageCount' => $pageCount,
                'limit' => $limit,
                'page' => 1,
                'start' => 0,
            );
            $upgradeSetting[$funcName] = $startData;
            $this->getSettingService()->set(self::SETTING_KEY, $upgradeSetting);
            $this->logger('info', 'startPage:set'.json_encode($this->getSettingService()->get(self::SETTING_KEY, array())));
            return $startData;
        }
        
        if ($upgradeSetting[$funcName]['page'] > $upgradeSetting[$funcName]['pageCount']) {
            return;
        }

        return $upgradeSetting[$funcName];
    }

    protected function endPage($funcName)
    {
        $upgradeSetting = $this->getSettingService()->get(self::SETTING_KEY, array());
        $this->logger('info', 'endPage:get'.json_encode($upgradeSetting));
        if ($upgradeSetting[$funcName]['page'] >= $upgradeSetting[$funcName]['pageCount']) {
            $upgradeSetting[$funcName] = array();
            $this->getSettingService()->set(self::SETTING_KEY, $upgradeSetting);
            $this->logger('info', 'endPage:set'.json_encode($upgradeSetting));
            return;
        } else {
            $upgradeSetting[$funcName]['page']++;
            $upgradeSetting[$funcName]['start'] = ($upgradeSetting[$funcName]['page'] - 1) * $upgradeSetting[$funcName]['limit'];
            $this->getSettingService()->set(self::SETTING_KEY, $upgradeSetting);
            $this->logger('info', 'endPage:set'.json_encode($upgradeSetting));
            return $upgradeSetting[$funcName]['page'];
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
        return $this->createService('System:SettingService');
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
        $data = date('Y-m-d H:i:s')." [{$level}] {$version} ".$message.PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'].'/../app/logs/upgrade.log';
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
