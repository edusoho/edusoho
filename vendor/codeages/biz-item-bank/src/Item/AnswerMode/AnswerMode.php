<?php

namespace Codeages\Biz\ItemBank\Item\AnswerMode;

use Codeages\Biz\ItemBank\Item\Type\Question;
use Codeages\Biz\ItemBank\Util\Validator\Validator;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\QuestionException;

abstract class AnswerMode
{
    const POINT_RIGHT = 'right';

    const POINT_WRONG = 'wrong';

    const POINT_NOANSWER = 'none';

    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function validate($responsePoints, $answer)
    {
        if (count($responsePoints) != count(array_column($responsePoints, static::INPUT_TYPE))) {
            throw new QuestionException('Field response_points is invalid.', ErrorCode::QUESTION_ARGUMENT_INVALID);
        }
    }

    public function isSubjective()
    {
        return static::IS_SUBJECTIVE;
    }

    public function filter($responsePoints)
    {
        return $responsePoints;
    }

    public function review($responsePoints, $answer, $response)
    {
        $responsePoints = array_column($responsePoints, static::INPUT_TYPE);
        $responsePointsResult = [];
        foreach ($responsePoints as $key => $responsePoint) {
            $responsePointsResult[$key] = $this->reviewPoint($key, $responsePoint, $answer, $response);
        }

        return [
            'result' => count($answer) == $this->getRightCount($responsePointsResult) && !$this->hasWrong($responsePointsResult) ? Question::REVIEW_RIGHT : Question::REVIEW_WRONG,
            'response_points_result' => $responsePointsResult,
        ];
    }

    public function parse($parsedQuestion, $question)
    {
        return $parsedQuestion;
    }

    public function getAnswerSceneQuestionReport($question, $questionReports)
    {
        $report = [
            'question_id' => $question['id'],
            'item_id' => $question['item_id'],
            'right_num' => 0,
            'wrong_num' => 0,
            'no_answer_num' => 0,
            'part_right_num' => 0,
            'response_points_report' => $this->getAnswerSceneResponsePointsReport($question, $questionReports),
        ];
        foreach ($questionReports as $questionReport) {
            isset($report[$questionReport['status'].'_num']) && $report[$questionReport['status'].'_num']++;
        }

        return $report;
    }

    abstract protected function reviewPoint($key, $responsePoint, $answer, $response);

    abstract protected function getAnswerSceneResponsePointsReport($question, $questionReports);

    protected function getRightCount($responsePointsResult)
    {
        $resultCount = array_count_values($responsePointsResult);
        if (empty($resultCount[self::POINT_RIGHT])) {
            return 0;
        }

        return $resultCount[self::POINT_RIGHT];
    }

    protected function hasWrong($responsePointsResult)
    {
        return in_array(self::POINT_WRONG, $responsePointsResult);
    }

    /**
     * @return Validator
     */
    protected function getValidator()
    {
        return $this->biz['validator'];
    }

    protected function purifyHtml($html)
    {
        return $this->biz['item_bank_html_helper']->purify($html);
    }
}
