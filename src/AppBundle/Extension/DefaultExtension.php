<?php
namespace AppBundle\Extension;

use Pimple\Container;
use Biz\Activity\Type\Live;
use Biz\Activity\Type\Text;
use Biz\Question\Type\Fill;
use Biz\Activity\Type\Audio;
use Biz\Activity\Type\Video;
use Biz\Question\Type\Essay;
use Biz\Question\Type\Choice;
use Biz\Activity\Type\Discuss;
use Biz\Activity\Type\Download;
use Biz\Question\Type\Material;
use Biz\Question\Type\Determine;
use Biz\Question\Type\SingleChoice;
use Pimple\ServiceProviderInterface;
use Biz\Question\Type\UncertainChoice;

class DefaultExtension extends Extension implements ServiceProviderInterface
{
    private $biz = null;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getBiz()
    {
        return $this->biz;
    }

    public function getQuestionTypes()
    {
        return array(
            'single_choice'    => array(
                'name'      => '单选题',
                'actions'   => array(
                    'create' => 'AppBundle:Question/SingleChoiceQuestion:create',
                    'edit'   => 'AppBundle:Question/SingleChoiceQuestion:edit',
                    'show'   => 'AppBundle:Question/SingleChoiceQuestion:show'
                ),
                'templates' => array(
                    'do' => 'question/single-choice-do.html.twig'
                )
            ),
            'choice'           => array(
                'name'      => '多选题',
                'actions'   => array(
                    'create' => 'AppBundle:Question/ChoiceQuestion:create',
                    'edit'   => 'AppBundle:Question/ChoiceQuestion:edit',
                    'show'   => 'AppBundle:Question/ChoiceQuestion:show'
                ),
                'templates' => array(
                    'do' => 'question/choice-do.html.twig'
                )
            ),
            'essay'            => array(
                'name'      => '问答题',
                'actions'   => array(
                    'create' => 'AppBundle:Question/EssayQuestion:create',
                    'edit'   => 'AppBundle:Question/EssayQuestion:edit',
                    'show'   => 'AppBundle:Question/EssayQuestion:show'
                ),
                'templates' => array(
                    'do' => 'question/essay-do.html.twig'
                )
            ),
            'uncertain_choice' => array(
                'name'      => '不定项选择题',
                'actions'   => array(
                    'create' => 'AppBundle:Question/UncertainChoiceQuesiton:create',
                    'edit'   => 'AppBundle:Question/UncertainChoiceQuesiton:edit',
                    'show'   => 'AppBundle:Question/UncertainChoiceQuesiton:show'
                ),
                'templates' => array(
                    'do' => 'question/uncertain-choice-do.html.twig'
                )
            ),
            'determine'        => array(
                'name'      => '判断题',
                'actions'   => array(
                    'create' => 'AppBundle:Question/DetermineQuestion:create',
                    'edit'   => 'AppBundle:Question/DetermineQuestion:edit',
                    'show'   => 'AppBundle:Question/DetermineQuestion:show'
                ),
                'templates' => array(
                    'do' => 'question/determine-do.html.twig'
                )
            ),
            'fill'             => array(
                'name'      => '填空题',
                'actions'   => array(
                    'create' => 'AppBundle:Question/FillQuestion:create',
                    'edit'   => 'AppBundle:Question/FillQuestion:edit',
                    'show'   => 'AppBundle:Question/FillQuestion:show'
                ),
                'templates' => array(
                    'do' => 'question/fill-do.html.twig'
                )
            ),
            'material'         => array(
                'name'      => '材料题',
                'actions'   => array(
                    'create' => 'AppBundle:Question/MaterialQuestion:create',
                    'edit'   => 'AppBundle:Question/MaterialQuestion:edit',
                    'show'   => 'AppBundle:Question/MaterialQuestion:show'
                ),
                'templates' => array(
                    'do' => 'question/material-do.html.twig'
                )
            )
        );
    }

    public function getActivities()
    {
        return array(
            'text'     => array(
                'meta'      => array(
                    'name' => '图文',
                    'icon' => 'es-icon es-icon-graphicclass'
                ),
                'actions'   => array(
                    'create' => 'AppBundle:Activity/Text:create',
                    'edit'   => 'AppBundle:Activity/Text:edit',
                    'show'   => 'AppBundle:Activity/Text:show'
                ),
                'templates' => array()

            ),
            'video'    => array(
                'meta'      => array(
                    'name' => '视频',
                    'icon' => 'es-icon es-icon-videoclass'
                ),
                'actions'   => array(
                    'create' => 'AppBundle:Activity/Video:create',
                    'edit'   => 'AppBundle:Activity/Video:edit',
                    'show'   => 'AppBundle:Activity/Video:show'
                ),
                'templates' => array()
            ),
            'audio'    => array(
                'meta'      => array(
                    'name' => '音频',
                    'icon' => 'es-icon es-icon-audioclass'
                ),
                'actions'   => array(
                    'create' => 'AppBundle:Activity/Audio:create',
                    'edit'   => 'AppBundle:Activity/Audio:edit',
                    'show'   => 'AppBundle:Activity/Audio:show'
                ),
                'templates' => array()
            ),
            'download' => array(
                'meta'      => array(
                    'name' => '下载资料',
                    'icon' => 'es-icon es-icon-filedownload'
                ),
                'actions'   => array(
                    'create' => 'AppBundle:Activity/Download:create',
                    'edit'   => 'AppBundle:Activity/Download:edit',
                    'show'   => 'AppBundle:Activity/Download:show'
                ),
                'templates' => array()
            ),
            'live'     => array(
                'meta'      => array(
                    'name' => '直播',
                    'icon' => 'es-icon es-icon-videocam'
                ),
                'actions'   => array(
                    'create' => 'AppBundle:Activity/Live:create',
                    'edit'   => 'AppBundle:Activity/Live:edit',
                    'show'   => 'AppBundle:Activity/Live:show'
                ),
                'templates' => array()
            ),
            'discuss'  => array(
                'meta'      => array(
                    'name' => '讨论',
                    'icon' => 'es-icon es-icon-comment'
                ),
                'actions'   => array(
                    'create' => 'AppBundle:Activity/Discuss:create',
                    'edit'   => 'AppBundle:Activity/Discuss:edit',
                    'show'   => 'AppBundle:Activity/Discuss:show'
                ),
                'templates' => array()
            ),
            'flash'  => array(
                'meta'      => array(
                    'name' => 'Flash',
                    'icon' => 'es-icon es-icon-flashclass'
                ),
                'actions'   => array(
                    'create' => 'AppBundle:Activity/Flash:create',
                    'edit'   => 'AppBundle:Activity/Flash:edit',
                    'show'   => 'AppBundle:Activity/Flash:show'
                ),
                'templates' => array()
            ),
            'doc'  => array(
                'meta'      => array(
                    'name' => '文档',
                    'icon' => 'es-icon es-icon-docclass'
                ),
                'actions'   => array(
                    'create' => 'AppBundle:Activity/Doc:create',
                    'edit'   => 'AppBundle:Activity/Doc:edit',
                    'show'   => 'AppBundle:Activity/Doc:show'
                ),
                'templates' => array()
            ),
            'ppt'  => array(
                'meta'      => array(
                    'name' => 'Ppt',
                    'icon' => 'es-icon es-icon-pptclass'
                ),
                'actions'   => array(
                    'create' => 'AppBundle:Activity/Ppt:create',
                    'edit'   => 'AppBundle:Activity/Ppt:edit',
                    'show'   => 'AppBundle:Activity/Ppt:show'
                ),
                'templates' => array()
            ),
        );
    }

    public function register(Container $container)
    {
        $this->registerQuestionTypes($container);

        $this->registerActivityTypes($container);
    }

    protected function registerActivityTypes($container)
    {
        $that                                = $this;
        $container['activity_type.text']     = function () use ($that) {
            return new Text($that->getBiz());
        };
        $container['activity_type.video']    = function () use ($that) {
            return new Video($that->getBiz());
        };
        $container['activity_type.audio']    = function () use ($that) {
            return new Audio($that->getBiz());
        };
        $container['activity_type.download'] = function () use ($that) {
            return new Download($that->getBiz());
        };
        $container['activity_type.live'] = function () use ($that) {
            return new Live($that->getBiz());
        };
        $container['activity_type.discuss'] = function () use ($that) {
            return new Discuss($that->getBiz());
        };
    }

    protected function registerQuestionTypes($container)
    {
        $container['question_type.choice'] = function () {
            return new Choice();
        };
        $container['question_type.single_choice'] = function () {
            return new SingleChoice();
        };
        $container['question_type.uncertain_choice'] = function () {
            return new UncertainChoice();
        };
        $container['question_type.determine'] = function () {
            return new Determine();
        };
        $container['question_type.essay'] = function () {
            return new Essay();
        };
        $container['question_type.fill'] = function () {
            return new Fill();
        };
        $container['question_type.material'] = function () {
            return new Material();
        };
    }
}
