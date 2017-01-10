<?php
namespace AppBundle\Extension;

use Pimple\Container;
use Biz\Activity\Type\Doc;
use Biz\Activity\Type\Ppt;
use Biz\Activity\Type\Live;
use Biz\Activity\Type\Text;
use Biz\Question\Type\Fill;
use Biz\Activity\Type\Audio;
use Biz\Activity\Type\Flash;
use Biz\Activity\Type\Video;
use Biz\Question\Type\Essay;
use Biz\Question\Type\Choice;
use Biz\Activity\Type\Discuss;
use Biz\Activity\Type\Download;
use Biz\Activity\Type\Exercise;
use Biz\Activity\Type\Homework;
use Biz\Question\Type\Material;
use Biz\Activity\Type\Testpaper;
use Biz\Question\Type\Determine;
use Biz\Question\Type\SingleChoice;
use Pimple\ServiceProviderInterface;
use Biz\Question\Type\UncertainChoice;
use Biz\Testpaper\Pattern\QuestionTypePattern;

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

    public function getCourseShowMetas()
    {
        $widgets = array(
            'characteristic'     => array(
                'uri'  => 'AppBundle:Course/Course:characteristic',
                'type' => 'render'
            ),
            'otherCourse'        => array(
                'uri'  => 'AppBundle:Course/Course:otherCourse',
                'type' => 'render'
            ),
            'recommendClassroom' => array(
                'uri'  => 'course/widgets/recommend-classroom.html.twig',
                'type' => 'include'
            ),
            'teachers'           => array(
                'uri'  => 'AppBundle:Course/Course:teachers',
                'type' => 'render'
            ),
            'newestStudents'     => array(
                'uri'  => 'AppBundle:Course/Course:newestStudents',
                'type' => 'render'
            ),
            'studentActivity'    => array(
                'uri'  => 'course/widgets/student-activity.html.twig',
                'type' => 'include'
            )
        );

        return array(
            'for_member' => array(
                'header'  => 'AppBundle:My/Course:headerForMember',
                'tabs'    => array(
                    'tasks'    => array(
                        'name'    => '目录',
                        'content' => 'AppBundle:Course/Course:tasks'
                    ),
                    'threads'  => array(
                        'name'    => '话题',
                        'number'  => 'threadNum',
                        'content' => 'AppBundle:Course/Thread:index'
                    ),
                    'reviews'  => array(
                        'name'    => '评价',
                        'number'  => 'ratingNum',
                        'content' => 'AppBundle:Course/Course:reviews'
                    ),
                    'notes'    => array(
                        'name'    => '笔记',
                        'number'  => 'noteNum',
                        'content' => 'AppBundle:Course/Course:notes'
                    ),
                    'material' => array(
                        'name'    => '资料区',
                        'number'  => 'materialNum',
                        'content' => 'AppBundle:Course/Material:index'
                    ),
                    'summary'  => array(
                        'name'    => '介绍',
                        'content' => 'AppBundle:Course/Course:summary'
                    )
                ),
                'widgets' => $widgets
            ),
            'for_guest' => array(
                'header'  => 'AppBundle:Course/Course:header',
                'tabs'    => array(
                    'summary' => array(
                        'name'    => '介绍',
                        'content' => 'AppBundle:Course/Course:summary'
                    ),
                    'tasks'   => array(
                        'name'    => '目录',
                        'content' => 'AppBundle:Course/Course:tasks'
                    ),
                    'reviews' => array(
                        'name'    => '评价',
                        'number'  => 'ratingNum',
                        'content' => 'AppBundle:Course/Course:reviews'
                    ),
                    'notes'   => array(
                        'name'    => '笔记',
                        'number'  => 'noteNum',
                        'content' => 'AppBundle:Course/Course:notes'
                    )
                ),
                'widgets' => $widgets
            )
        );
    }

    public function getQuestionTypes()
    {
        return array(
            'single_choice'    => array(
                'name'         => '单选题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/SingleChoiceQuestion:create',
                    'edit'   => 'AppBundle:Question/SingleChoiceQuestion:edit',
                    'show'   => 'AppBundle:Question/SingleChoiceQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/single-choice-do.html.twig'
                ),
                'hasMissScore' => 0
            ),
            'choice'           => array(
                'name'         => '多选题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/ChoiceQuestion:create',
                    'edit'   => 'AppBundle:Question/ChoiceQuestion:edit',
                    'show'   => 'AppBundle:Question/ChoiceQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/choice-do.html.twig'
                ),
                'hasMissScore' => 1
            ),
            'essay'            => array(
                'name'         => '问答题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/EssayQuestion:create',
                    'edit'   => 'AppBundle:Question/EssayQuestion:edit',
                    'show'   => 'AppBundle:Question/EssayQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/essay-do.html.twig'
                ),
                'hasMissScore' => 0
            ),
            'uncertain_choice' => array(
                'name'         => '不定项选择题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/UncertainChoiceQuesiton:create',
                    'edit'   => 'AppBundle:Question/UncertainChoiceQuesiton:edit',
                    'show'   => 'AppBundle:Question/UncertainChoiceQuesiton:show'
                ),
                'templates'    => array(
                    'do' => 'question/uncertain-choice-do.html.twig'
                ),
                'hasMissScore' => 1
            ),
            'determine'        => array(
                'name'         => '判断题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/DetermineQuestion:create',
                    'edit'   => 'AppBundle:Question/DetermineQuestion:edit',
                    'show'   => 'AppBundle:Question/DetermineQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/determine-do.html.twig'
                ),
                'hasMissScore' => 0
            ),
            'fill'             => array(
                'name'         => '填空题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/FillQuestion:create',
                    'edit'   => 'AppBundle:Question/FillQuestion:edit',
                    'show'   => 'AppBundle:Question/FillQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/fill-do.html.twig'
                ),
                'hasMissScore' => 0
            ),
            'material'         => array(
                'name'         => '材料题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/MaterialQuestion:create',
                    'edit'   => 'AppBundle:Question/MaterialQuestion:edit',
                    'show'   => 'AppBundle:Question/MaterialQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/material-do.html.twig'
                ),
                'hasMissScore' => 0
            )
        );
    }

    public function getActivities()
    {
        return array(
            'text'      => array(
                'meta'    => array(
                    'name' => '图文',
                    'icon' => 'es-icon es-icon-graphicclass'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Text:create',
                    'edit'            => 'AppBundle:Activity/Text:edit',
                    'show'            => 'AppBundle:Activity/Text:show',
                    'preview'         => 'AppBundle:Activity/Text:preview',
                    'finishCondition' => 'AppBundle:Activity/Text:finishCondition'
                )
            ),
            'video'     => array(
                'meta'    => array(
                    'name' => '视频',
                    'icon' => 'es-icon es-icon-videoclass'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Video:create',
                    'edit'            => 'AppBundle:Activity/Video:edit',
                    'show'            => 'AppBundle:Activity/Video:show',
                    'preview'         => 'AppBundle:Activity/Video:preview',
                    'finishCondition' => 'AppBundle:Activity/Video:finishCondition'
                )
            ),
            'audio'     => array(
                'meta'    => array(
                    'name' => '音频',
                    'icon' => 'es-icon es-icon-audioclass'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Audio:create',
                    'edit'            => 'AppBundle:Activity/Audio:edit',
                    'show'            => 'AppBundle:Activity/Audio:show',
                    'preview'         => 'AppBundle:Activity/Audio:preview',
                    'finishCondition' => 'AppBundle:Activity/Audio:finishCondition'
                )
            ),
            'download'  => array(
                'meta'    => array(
                    'name' => '下载资料',
                    'icon' => 'es-icon es-icon-filedownload'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Download:create',
                    'edit'            => 'AppBundle:Activity/Download:edit',
                    'show'            => 'AppBundle:Activity/Download:show',
                    'preview'         => 'AppBundle:Activity/Download:preview',
                    'finishCondition' => 'AppBundle:Activity/Download:finishCondition'
                )
            ),
            'live'      => array(
                'meta'    => array(
                    'name' => '直播',
                    'icon' => 'es-icon es-icon-videocam'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Live:create',
                    'edit'            => 'AppBundle:Activity/Live:edit',
                    'show'            => 'AppBundle:Activity/Live:show',
                    'preview'         => 'AppBundle:Activity/Live:preview',
                    'finishCondition' => 'AppBundle:Activity/Live:finishCondition'
                )
            ),
            'discuss'   => array(
                'meta'    => array(
                    'name' => '讨论',
                    'icon' => 'es-icon es-icon-comment'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Discuss:create',
                    'edit'            => 'AppBundle:Activity/Discuss:edit',
                    'show'            => 'AppBundle:Activity/Discuss:show',
                    'preview'         => 'AppBundle:Activity/Discuss:preview',
                    'finishCondition' => 'AppBundle:Activity/Discuss:finishCondition'
                )
            ),

            'flash'     => array(
                'meta'    => array(
                    'name' => 'Flash',
                    'icon' => 'es-icon es-icon-flashclass'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Flash:create',
                    'edit'            => 'AppBundle:Activity/Flash:edit',
                    'show'            => 'AppBundle:Activity/Flash:show',
                    'preview'         => 'AppBundle:Activity/Flash:preview',
                    'finishCondition' => 'AppBundle:Activity/Flash:finishCondition'
                )
            ),
            'doc'       => array(
                'meta'    => array(
                    'name' => '文档',
                    'icon' => 'es-icon es-icon-description'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Doc:create',
                    'edit'            => 'AppBundle:Activity/Doc:edit',
                    'show'            => 'AppBundle:Activity/Doc:show',
                    'preview'         => 'AppBundle:Activity/Doc:preview',
                    'finishCondition' => 'AppBundle:Activity/Doc:finishCondition'
                )
            ),
            'ppt'       => array(
                'meta'    => array(
                    'name' => 'PPT',
                    'icon' => 'es-icon es-icon-pptclass'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Ppt:create',
                    'edit'            => 'AppBundle:Activity/Ppt:edit',
                    'show'            => 'AppBundle:Activity/Ppt:show',
                    'preview'         => 'AppBundle:Activity/Ppt:preview',
                    'finishCondition' => 'AppBundle:Activity/Ppt:finishCondition'
                )
            ),
            'testpaper' => array(
                'meta'    => array(
                    'name' => '考试',
                    'icon' => 'es-icon es-icon-lesson'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Testpaper:create',
                    'edit'            => 'AppBundle:Activity/Testpaper:edit',
                    'show'            => 'AppBundle:Activity/Testpaper:show',
                    'preview'         => 'AppBundle:Activity/Testpaper:tryLook',
                    'finishCondition' => 'AppBundle:Activity/Testpaper:finishCondition'
                )
            ),
            'homework'  => array(
                'meta'    => array(
                    'name' => '作业',
                    'icon' => 'es-icon es-icon-exam'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Homework:create',
                    'edit'            => 'AppBundle:Activity/Homework:edit',
                    'preview'         => 'AppBundle:Activity/Homework:tryLook',
                    'show'            => 'AppBundle:Activity/Homework:show',
                    'finishCondition' => 'AppBundle:Activity/Homework:finishCondition'
                )
            ),
            'exercise'  => array(
                'meta'    => array(
                    'name' => '练习',
                    'icon' => 'es-icon es-icon-mylibrarybooks'
                ),
                'actions' => array(
                    'create'          => 'AppBundle:Activity/Exercise:create',
                    'edit'            => 'AppBundle:Activity/Exercise:edit',
                    'show'            => 'AppBundle:Activity/Exercise:show',
                    'preview'         => 'AppBundle:Activity/Exercise:tryLook',
                    'finishCondition' => 'AppBundle:Activity/Exercise:finishCondition'
                )
            )
        );
    }

    public function register(Container $container)
    {
        $this->registerQuestionTypes($container);

        $this->registerActivityTypes($container);
    }

    protected function registerActivityTypes($container)
    {
        $that                            = $this;
        $container['activity_type.text'] = function () use ($that) {
            return new Text($that->getBiz());
        };
        $container['activity_type.video'] = function () use ($that) {
            return new Video($that->getBiz());
        };
        $container['activity_type.audio'] = function () use ($that) {
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

        $container['activity_type.flash'] = function () use ($that) {
            return new Flash($that->getBiz());
        };

        $container['activity_type.doc'] = function () use ($that) {
            return new Doc($that->getBiz());
        };

        $container['activity_type.ppt'] = function () use ($that) {
            return new Ppt($that->getBiz());
        };
        $container['activity_type.testpaper'] = function () use ($that) {
            return new Testpaper($that->getBiz());
        };
        $container['activity_type.homework'] = function () use ($that) {
            return new Homework($that->getBiz());
        };
        $container['activity_type.exercise'] = function () use ($that) {
            return new Exercise($that->getBiz());
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

        $container['testpaper_pattern.questionType'] = function ($container) {
            return new QuestionTypePattern($container);
        };

    }
}
