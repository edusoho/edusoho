<?php

namespace AppBundle\Command;

use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMissingAnswerQuestionReportCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('fix-data:answer-question-report')
            ->addArgument('recordId', InputArgument::REQUIRED, '答题记录id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $answerRecord = $this->getAnswerRecordService()->get($input->getArgument('recordId'));
        if (empty($answerRecord)) {
            return;
        }
        if ($this->getAssessmentService()->isEmptyAssessment($answerRecord['assessment_id'])) {
            return;
        }
        $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecord['id']);
        if ($answerQuestionReports) {
            return;
        }
        $sections = $this->getAssessmentSectionService()->findSectionDetailByAssessmentId($answerRecord['assessment_id']);

        $answerQuestionReports = [];
        $newAnswerQuestionReport = [
            'answer_record_id' => $answerRecord['id'],
            'assessment_id' => $answerRecord['assessment_id'],
            'status' => AnswerQuestionReportService::STATUS_NOANSWER,
        ];

        foreach ($sections as $section) {
            $newAnswerQuestionReport['section_id'] = $section['id'];
            foreach ($section['items'] as $item) {
                $newAnswerQuestionReport['item_id'] = $item['id'];
                foreach ($item['questions'] as $question) {
                    $newAnswerQuestionReport['question_id'] = $question['id'];
                    $newAnswerQuestionReport['identify'] = $answerRecord['id'].'_'.$question['id'];
                    $answerQuestionReports[] = $newAnswerQuestionReport;
                }
            }
        }

        $this->getAnswerQuestionReportService()->batchCreate($answerQuestionReports);
    }

    /**
     * @return AnswerRecordService
     */
    private function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AssessmentSectionService
     */
    protected function getAssessmentSectionService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentSectionService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->createService('ItemBank:Answer:AnswerQuestionReportService');
    }
}
