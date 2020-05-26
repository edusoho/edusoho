<?php

namespace Codeages\Biz\ItemBank;

use Codeages\Biz\ItemBank\Assessment\Util\AssessmentReview;
use Codeages\Biz\ItemBank\Assessment\Util\ItemDraw;
use Codeages\Biz\ItemBank\Assessment\ScoreRule\ScoreRuleProcessor;
use Codeages\Biz\ItemBank\Item\AnswerMode\AnswerModeFactory;
use Codeages\Biz\ItemBank\Item\ItemParser;
use Codeages\Biz\ItemBank\Item\Type\ItemFactory;
use Codeages\Biz\ItemBank\Item\Type\Question;
use Codeages\Biz\ItemBank\Item\Wrapper\AttachmentWrapper;
use Codeages\Biz\ItemBank\Item\Wrapper\ExportItemsWrapper;
use Codeages\Biz\ItemBank\Item\Wrapper\ItemWrapper;
use Codeages\Biz\ItemBank\Item\Wrapper\QuestionWrapper;
use Codeages\Biz\ItemBank\Util\HTMLHelper;
use Codeages\Biz\ItemBank\Util\Validator\Validator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ItemBankServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(__DIR__).'/migrations';

        $biz['autoload.aliases']['ItemBank'] = 'Codeages\Biz\ItemBank';

        $biz['item_bank_html_helper'] = function ($biz) {
            return new HTMLHelper($biz);
        };

        $biz['validator'] = $biz->factory(function () {
            return new Validator();
        });

        $biz['item_draw_helper'] = function ($biz) {
            return new ItemDraw($biz);
        };

        $biz['assessment_review_helper'] = function ($biz) {
            return new AssessmentReview($biz);
        };

        $biz['item_parser'] = function ($biz) {
            return new ItemParser($biz);
        };

        $this->registerWrapper($biz);

        $this->registerItemType($biz);

        $this->registerAnswerMode($biz);

        $this->registerScoreRule($biz);
    }

    public function registerWrapper(Container $biz)
    {
        $biz['item_wrapper'] = function ($biz) {
            return new ItemWrapper($biz);
        };

        $biz['question_wrapper'] = function ($biz) {
            return new QuestionWrapper($biz);
        };

        $biz['export_items_wrapper'] = function ($biz) {
            return new ExportItemsWrapper($biz);
        };

        $biz['item_attachment_wrapper'] = function ($biz) {
            return new AttachmentWrapper($biz);
        };
    }

    public function registerItemType(Container $biz)
    {
        $items = [
            '\Codeages\Biz\ItemBank\Item\Type\SingleChoiceItem',
            '\Codeages\Biz\ItemBank\Item\Type\UncertainChoiceItem',
            '\Codeages\Biz\ItemBank\Item\Type\ChoiceItem',
            '\Codeages\Biz\ItemBank\Item\Type\DetermineItem',
            '\Codeages\Biz\ItemBank\Item\Type\FillItem',
            '\Codeages\Biz\ItemBank\Item\Type\EssayItem',
            '\Codeages\Biz\ItemBank\Item\Type\MaterialItem',
        ];

        foreach ($items as $item) {
            $biz['item_type.'.$item::TYPE] = function ($biz) use ($item) {
                return new $item($biz);
            };
        }

        $biz['item_type_factory'] = function ($biz) {
            return new ItemFactory($biz);
        };

        $biz['question_processor'] = function ($biz) {
            return new Question($biz);
        };
    }

    public function registerAnswerMode(Container $biz)
    {
        $answerModes = [
            '\Codeages\Biz\ItemBank\Item\AnswerMode\SingleChoiceAnswerMode',
            '\Codeages\Biz\ItemBank\Item\AnswerMode\UncertainChoiceAnswerMode',
            '\Codeages\Biz\ItemBank\Item\AnswerMode\ChoiceAnswerMode',
            '\Codeages\Biz\ItemBank\Item\AnswerMode\TrueFalseAnswerMode',
            '\Codeages\Biz\ItemBank\Item\AnswerMode\TextAnswerMode',
            '\Codeages\Biz\ItemBank\Item\AnswerMode\RichTextAnswerMode',
        ];

        foreach ($answerModes as $answerMode) {
            $biz['answer_mode.'.$answerMode::NAME] = function ($biz) use ($answerMode) {
                return new $answerMode($biz);
            };
        }

        $biz['answer_mode_factory'] = function ($biz) {
            return new AnswerModeFactory($biz);
        };
    }

    protected function registerScoreRule(Container $biz)
    {
        $scoreRules = [
            'all_right' => [
                'class' => '\Codeages\Biz\ItemBank\Assessment\ScoreRule\AllRightScoreRule',
            ],
            'part_right' => [
                'class' => '\Codeages\Biz\ItemBank\Assessment\ScoreRule\PartRightScoreRule',
            ],
            'no_answer' => [
                'class' => '\Codeages\Biz\ItemBank\Assessment\ScoreRule\NoAnswerScoreRule',
            ],
            'wrong' => [
                'class' => '\Codeages\Biz\ItemBank\Assessment\ScoreRule\WrongScoreRule',
            ],
        ];

        foreach ($scoreRules as $key => $scoreRule) {
            $biz['score_rule.'.$key] = function ($biz) use ($scoreRule) {
                return new $scoreRule['class']($biz);
            };
        }

        $biz['score_rule_processor'] = function ($biz) {
            return new ScoreRuleProcessor($biz);
        };

        $biz['score_rules'] = $scoreRules;
    }
}
