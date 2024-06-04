<?php

namespace Biz\AI\Job;

use Biz\CloudData\Service\CloudDataService;
use Biz\System\Constant\LogAction;
use Biz\System\Constant\LogModule;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class AIQuestionAnalysisLogReportJob extends AbstractJob
{
    public function execute()
    {
        $startDate = date('Y-m-d', strtotime('-1 day'));
        $endDate = date('Y-m-d');
        $teacherLogCount = $this->getLogService()->searchLogCount(['module' => LogModule::AI, 'action' => LogAction::TEACHER_GENERATE_QUESTION_ANALYSIS, 'startDateTime' => $startDate, 'endDateTime' => $endDate]);
        $studentLogs = $this->getLogService()->searchLogs(['module' => LogModule::AI, 'action' => LogAction::STUDENT_GENERATE_QUESTION_ANALYSIS, 'startDateTime' => $startDate, 'endDateTime' => $endDate], [], 0, PHP_INT_MAX);
        if (empty($teacherLogCount) && empty($studentLogs)) {
            return;
        }
        $body = [
            'student' => [
                'count' => [
                    'course-testpaper' => 0,
                    'course-homework' => 0,
                    'course-exercise' => 0,
                    'itembank-chapter' => 0,
                    'itembank-assessment' => 0,
                    'wrong-question' => 0,
                    'unknown' => 0,
                ],
            ],
            'teacher' => [
                'count' => $teacherLogCount,
            ],
        ];
        foreach ($studentLogs as $studentLog) {
            if (isset($body['student']['count'][$studentLog['data']['scene']])) {
                $body['student']['count'][$studentLog['data']['scene']]++;
            }
        }
        $this->getCloudDataService()->push('ai.generate.question_analysis', $body);
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return CloudDataService
     */
    private function getCloudDataService()
    {
        return $this->biz->service('CloudData:CloudDataService');
    }
}
