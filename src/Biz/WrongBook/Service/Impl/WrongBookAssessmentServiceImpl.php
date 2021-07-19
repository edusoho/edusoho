<?php

namespace Biz\WrongBook\Service\Impl;

use Biz\WrongBook\Service\WrongBookAssessmentService;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Assessment\Service\Impl\AssessmentServiceImpl;

class WrongBookAssessmentServiceImpl extends AssessmentServiceImpl implements WrongBookAssessmentService
{
    /**
     * @param $assessment
     *
     * @return array
     *
     * @throws \Exception
     */
    public function createAssessment($assessment)
    {
        try {
            $this->beginTransaction();

            $basicAssessment = $this->createBasicAssessment($assessment);
            $assessment = $this->createAssessmentSectionsAndItems($basicAssessment['id'], $assessment['sections']);

            $this->commit();

            return $assessment;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function createBasicAssessment($assessment)
    {
        $user = $this->biz['user'];
        $currentUserId = empty($user['id']) ? 0 : $user['id'];
        $assessment = $this->getValidator()->validate($assessment, [
            'bank_id' => ['integer', ['min', 0]],
            'name' => ['required', ['lengthBetween', 1, 255]],
            'description' => [],
            'created_user_id' => ['integer', ['min', 0]],
            'item_count' => ['integer', ['min', 0]],
            'question_count' => ['integer', ['min', 0]],
            'displayable' => ['required', ['in', [0, 1]]],
        ]);

        isset($assessment['description']) && $assessment['description'] = $this->biz['item_bank_html_helper']->purify($assessment['description']);
        $assessment['created_user_id'] = empty($assessment['created_user_id']) ? $currentUserId : $assessment['created_user_id'];
        $assessment['updated_user_id'] = $assessment['created_user_id'];

        $assessment = $this->getAssessmentDao()->create($assessment);

        if (1 === (int) $assessment['displayable']) {
            $this->getItemBankService()->updateAssessmentNum($assessment['bank_id'], 1);
        }

        $this->dispatch('assessment.create', $assessment);

        return $assessment;
    }

    protected function createAssessmentSectionsAndItems($assessmentId, $sections)
    {
        $assessmentSections = [];
        $sections = $this->setSeq($sections);
        foreach ($sections as $section) {
            if (empty($section['items'])) {
                continue;
            }
            $assessmentSections[] = $this->getSectionService()->createAssessmentSection($assessmentId, $section);
        }

        $assessment = $this->updateBasicAssessment($assessmentId, [
            'total_score' => array_sum(ArrayToolkit::column($assessmentSections, 'total_score')),
            'item_count' => array_sum(ArrayToolkit::column($assessmentSections, 'item_count')),
            'question_count' => array_sum(ArrayToolkit::column($assessmentSections, 'question_count')),
        ]);
        $assessment['sections'] = $assessmentSections;

        return $assessment;
    }

    protected function setSeq($sections)
    {
        $sectionSeq = 1;
        $itemSeq = 1;
        $questionSeq = 1;
        foreach ($sections as &$section) {
            $section['seq'] = $sectionSeq++;
            foreach ($section['items'] as &$item) {
                $item['seq'] = $itemSeq++;
                foreach ($item['questions'] as &$question) {
                    $question['seq'] = $questionSeq++;
                }
            }
        }

        return $sections;
    }
}
