<?php

namespace AppBundle\Extension;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Type\Audio;
use Biz\Activity\Type\Discuss;
use Biz\Activity\Type\Doc;
use Biz\Activity\Type\Download;
use Biz\Activity\Type\Exercise;
use Biz\Activity\Type\Flash;
use Biz\Activity\Type\Homework;
use Biz\Activity\Type\Live;
use Biz\Activity\Type\Ppt;
use Biz\Activity\Type\Testpaper;
use Biz\Activity\Type\Text;
use Biz\Activity\Type\Video;
use Biz\Question\Type\Choice;
use Biz\Question\Type\Determine;
use Biz\Question\Type\Essay;
use Biz\Question\Type\Fill;
use Biz\Question\Type\Material;
use Biz\Question\Type\SingleChoice;
use Biz\Question\Type\UncertainChoice;
use Biz\Testpaper\Pattern\QuestionTypePattern;
use Codeages\Biz\Framework\Context\Biz;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DefaultExtension extends Extension implements ServiceProviderInterface
{
    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz)
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
            //课程特色
            'characteristic' => array(
                'uri' => 'AppBundle:Course/Course:characteristic',
                'renderType' => 'render',
            ),
            //其他教学计划
            'otherCourse' => array(
                'uri' => 'AppBundle:Course/Course:otherCourse',
                'renderType' => 'render',
            ),
            //所属班级
            'belongClassroom' => array(
                'uri' => 'course/widgets/belong-classroom.html.twig',
                'renderType' => 'include',
                'showMode' => 'classroom', //班级课程才会显示
            ),
            //推荐班级
            'recommendClassroom' => array(
                'uri' => 'course/widgets/recommend-classroom.html.twig',
                // 'uri'        => 'default/recommend-classroom.html.twig',
                'renderType' => 'include',
                'showMode' => 'course', //普通课程才会显示
            ),
            //教学团队
            'teachers' => array(
                'uri' => 'AppBundle:Course/Course:teachers',
                'renderType' => 'render',
            ),
            //最新学员
            'newestStudents' => array(
                'uri' => 'AppBundle:Course/Course:newestStudents',
                'renderType' => 'render',
            ),
            //学员动态
            'studentActivity' => array(
                'uri' => 'course/widgets/student-activity.html.twig',
                'renderType' => 'include',
            ),
        );

        $forGuestWidgets = array(
            'recommendClassroom' => $widgets['recommendClassroom'],
            'characteristic' => $widgets['characteristic'],
            'teachers' => $widgets['teachers'],
            'newestStudents' => $widgets['newestStudents'],
            'studentActivity' => $widgets['studentActivity'],
        );

        $forMemberWidgets = array(
            'belongClassroom' => $widgets['belongClassroom'],
            'recommendClassroom' => $widgets['recommendClassroom'],
            'teachers' => $widgets['teachers'],
            'newestStudents' => $widgets['newestStudents'],
            'studentActivity' => $widgets['studentActivity'],
        );

        return array(
            'for_member' => array(
                'header' => 'AppBundle:My/Course:headerForMember',
                'tabs' => array(
                    'tasks' => array(
                        'name' => '目录',
                        'content' => 'AppBundle:Course/Course:tasks',
                    ),
                    'threads' => array(
                        'name' => '话题',
                        'number' => 'threadNum',
                        'content' => 'AppBundle:Course/Thread:index',
                    ),
                    'notes' => array(
                        'name' => '笔记',
                        'number' => 'noteNum',
                        'content' => 'AppBundle:Course/Course:notes',
                    ),
                    'material' => array(
                        'name' => '资料区',
                        'number' => 'materialNum',
                        'content' => 'AppBundle:Course/Material:index',
                    ),
                    'reviews' => array(
                        'name' => '评价',
                        'number' => 'ratingNum',
                        'content' => 'AppBundle:Course/Course:reviews',
                    ),
                    'summary' => array(
                        'name' => '介绍',
                        'content' => 'AppBundle:Course/Course:summary',
                    ),
                ),
                'widgets' => $forMemberWidgets,
            ),
            'for_guest' => array(
                'header' => 'AppBundle:Course/Course:header',
                'tabs' => array(
                    'summary' => array(
                        'name' => '介绍',
                        'content' => 'AppBundle:Course/Course:summary',
                    ),
                    'tasks' => array(
                        'name' => '目录',
                        'content' => 'AppBundle:Course/Course:tasks',
                    ),
                    'reviews' => array(
                        'name' => '评价',
                        'number' => 'ratingNum',
                        'content' => 'AppBundle:Course/Course:reviews',
                    ),
                    'notes' => array(
                        'name' => '笔记',
                        'number' => 'noteNum',
                        'content' => 'AppBundle:Course/Course:notes',
                    ),
                ),
                'widgets' => $forGuestWidgets,
            ),
        );
    }

    public function getQuestionTypes()
    {
        return array(
            'single_choice' => array(
                'name' => '单选题',
                'actions' => array(
                    'create' => 'AppBundle:Question/SingleChoiceQuestion:create',
                    'edit' => 'AppBundle:Question/SingleChoiceQuestion:edit',
                    'show' => 'AppBundle:Question/SingleChoiceQuestion:show',
                ),
                'templates' => array(
                    'do' => 'question/single-choice-do.html.twig',
                ),
                'hasMissScore' => 0,
            ),
            'choice' => array(
                'name' => '多选题',
                'actions' => array(
                    'create' => 'AppBundle:Question/ChoiceQuestion:create',
                    'edit' => 'AppBundle:Question/ChoiceQuestion:edit',
                    'show' => 'AppBundle:Question/ChoiceQuestion:show',
                ),
                'templates' => array(
                    'do' => 'question/choice-do.html.twig',
                ),
                'hasMissScore' => 1,
            ),
            'essay' => array(
                'name' => '问答题',
                'actions' => array(
                    'create' => 'AppBundle:Question/EssayQuestion:create',
                    'edit' => 'AppBundle:Question/EssayQuestion:edit',
                    'show' => 'AppBundle:Question/EssayQuestion:show',
                ),
                'templates' => array(
                    'do' => 'question/essay-do.html.twig',
                ),
                'hasMissScore' => 0,
            ),
            'uncertain_choice' => array(
                'name' => '不定项选择题',
                'actions' => array(
                    'create' => 'AppBundle:Question/UncertainChoiceQuesiton:create',
                    'edit' => 'AppBundle:Question/UncertainChoiceQuesiton:edit',
                    'show' => 'AppBundle:Question/UncertainChoiceQuesiton:show',
                ),
                'templates' => array(
                    'do' => 'question/uncertain-choice-do.html.twig',
                ),
                'hasMissScore' => 1,
            ),
            'determine' => array(
                'name' => '判断题',
                'actions' => array(
                    'create' => 'AppBundle:Question/DetermineQuestion:create',
                    'edit' => 'AppBundle:Question/DetermineQuestion:edit',
                    'show' => 'AppBundle:Question/DetermineQuestion:show',
                ),
                'templates' => array(
                    'do' => 'question/determine-do.html.twig',
                ),
                'hasMissScore' => 0,
            ),
            'fill' => array(
                'name' => '填空题',
                'actions' => array(
                    'create' => 'AppBundle:Question/FillQuestion:create',
                    'edit' => 'AppBundle:Question/FillQuestion:edit',
                    'show' => 'AppBundle:Question/FillQuestion:show',
                ),
                'templates' => array(
                    'do' => 'question/fill-do.html.twig',
                ),
                'hasMissScore' => 0,
            ),
            'material' => array(
                'name' => '材料题',
                'actions' => array(
                    'create' => 'AppBundle:Question/MaterialQuestion:create',
                    'edit' => 'AppBundle:Question/MaterialQuestion:edit',
                    'show' => 'AppBundle:Question/MaterialQuestion:show',
                ),
                'templates' => array(
                    'do' => 'question/material-do.html.twig',
                ),
                'hasMissScore' => 0,
            ),
        );
    }

    public function getActivities()
    {
        $biz = $this->getBiz();

        return array(
            'text' => array(
                'meta' => array(
                    'name' => '图文',
                    'icon' => 'es-icon es-icon-graphicclass',
                ),
                'controller' => 'AppBundle:Activity/Text',
                'visible' => function ($courseSet, $course) {
                    return $courseSet['type'] != 'live';
                },
            ),
            'video' => array(
                'meta' => array(
                    'name' => '视频',
                    'icon' => 'es-icon es-icon-videoclass',
                ),
                'controller' => 'AppBundle:Activity/Video',
                'visible' => function ($courseSet, $course) {
                    return $courseSet['type'] != 'live';
                },
            ),
            'audio' => array(
                'meta' => array(
                    'name' => '音频',
                    'icon' => 'es-icon es-icon-audioclass',
                ),
                'controller' => 'AppBundle:Activity/Audio',
                'visible' => function ($courseSet, $course) {
                    return $courseSet['type'] != 'live';
                },
            ),
            'live' => array(
                'meta' => array(
                    'name' => '直播',
                    'icon' => 'es-icon es-icon-videocam',
                ),
                'controller' => 'AppBundle:Activity/Live',
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('course');

                    return ArrayToolkit::get($storage, 'live_course_enabled', false);
                },
            ),
            'discuss' => array(
                'meta' => array(
                    'name' => '讨论',
                    'icon' => 'es-icon es-icon-comment',
                ),
                'controller' => 'AppBundle:Activity/Discuss',
                'visible' => function ($courseSet, $course) {
                    return true;
                },
            ),

            'flash' => array(
                'meta' => array(
                    'name' => 'Flash',
                    'icon' => 'es-icon es-icon-flashclass',
                ),
                'controller' => 'AppBundle:Activity/Flash',
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('storage');
                    $uploadMode = ArrayToolkit::get($storage, 'upload_mode', 'local');

                    return $uploadMode == 'cloud' && $courseSet['type'] != 'live';
                },
            ),
            'doc' => array(
                'meta' => array(
                    'name' => '文档',
                    'icon' => 'es-icon es-icon-description',
                ),
                'controller' => 'AppBundle:Activity/Doc',
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('storage');
                    $uploadMode = ArrayToolkit::get($storage, 'upload_mode', 'local');

                    return $uploadMode == 'cloud' && $courseSet['type'] != 'live';
                },
            ),
            'ppt' => array(
                'meta' => array(
                    'name' => 'PPT',
                    'icon' => 'es-icon es-icon-pptclass',
                ),
                'controller' => 'AppBundle:Activity/Ppt',
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('storage');
                    $uploadMode = ArrayToolkit::get($storage, 'upload_mode', 'local');

                    return $uploadMode == 'cloud' && $courseSet['type'] != 'live';
                },
            ),
            'testpaper' => array(
                'meta' => array(
                    'name' => '考试',
                    'icon' => 'es-icon es-icon-assignment-turned-in',
                ),
                'controller' => 'AppBundle:Activity/Testpaper',
                'visible' => function ($courseSet, $course) use ($biz) {
                    return true;
                },
            ),
            'homework' => array(
                'meta' => array(
                    'name' => '作业',
                    'icon' => 'es-icon es-icon-writefill',
                ),
                'controller' => 'AppBundle:Activity/Homework',
                'visible' => function ($courseSet, $course) use ($biz) {
                    return true;
                },
            ),
            'exercise' => array(
                'meta' => array(
                    'name' => '练习',
                    'icon' => 'es-icon es-icon-mylibrarybooks',
                ),
                'controller' => 'AppBundle:Activity/Exercise',
                'visible' => function ($courseSet, $course) use ($biz) {
                    return true;
                },
            ),
            'download' => array(
                'meta' => array(
                    'name' => '下载资料',
                    'icon' => 'es-icon es-icon-filedownload',
                ),
                'controller' => 'AppBundle:Activity/Download',
                'visible' => function ($courseSet, $course) {
                    return true;
                },
            ),
        );
    }

    protected function registerCourseCopyChain($container)
    {
        $chains = array(
            'course-set' => array(
                'clz' => 'Biz\Course\Copy\Impl\CourseSetCopy',
                'children' => array(
                    'course-set-testpaper' => array(
                        'clz' => 'Biz\Course\Copy\Impl\CourseSetTestpaperCopy',
                    ),
                    'course' => array(
                        'clz' => 'Biz\Course\Copy\Impl\CourseCopy',
                        'children' => array(
                            'task' => array(
                                'clz' => 'Biz\Course\Copy\Impl\TaskCopy',
                            ),
                        ),
                    ),
                ),
            ),
        );
        $that = $this;
        //used for course/courseSet copy
        $container['course_copy.chains'] = function ($node) use ($that, $chains) {
            return function ($node) use ($that, $chains) {
                return $that->arrayWalk($chains, $node);
            };
        };
    }

    public function arrayWalk($array, $key)
    {
        if (!empty($array[$key])) {
            return $array[$key];
        }
        $result = array();
        foreach ($array as $k => $value) {
            if (!empty($value['children']) && empty($result)) {
                $result = $this->arrayWalk($value['children'], $key);
            }
        }

        return $result;
    }

    public function register(Container $container)
    {
        $this->registerQuestionTypes($container);

        $this->registerActivityTypes($container);

        $this->registerCourseCopyChain($container);
    }

    protected function registerActivityTypes($container)
    {
        $container['activity_type.text'] = function ($biz) {
            return new Text($biz);
        };
        $container['activity_type.video'] = function ($biz) {
            return new Video($biz);
        };
        $container['activity_type.audio'] = function ($biz) {
            return new Audio($biz);
        };

        $container['activity_type.download'] = function ($biz) {
            return new Download($biz);
        };

        $container['activity_type.live'] = function ($biz) {
            return new Live($biz);
        };

        $container['activity_type.discuss'] = function ($biz) {
            return new Discuss($biz);
        };

        $container['activity_type.flash'] = function ($biz) {
            return new Flash($biz);
        };

        $container['activity_type.doc'] = function ($biz) {
            return new Doc($biz);
        };

        $container['activity_type.ppt'] = function ($biz) {
            return new Ppt($biz);
        };
        $container['activity_type.testpaper'] = function ($biz) {
            return new Testpaper($biz);
        };
        $container['activity_type.homework'] = function ($biz) {
            return new Homework($biz);
        };
        $container['activity_type.exercise'] = function ($biz) {
            return new Exercise($biz);
        };
    }

    protected function registerQuestionTypes($container)
    {
        $container['question_type.choice'] = function ($biz) {
            $obj = new Choice();
            $obj->setBiz($biz);

            return $obj;
        };
        $container['question_type.single_choice'] = function ($biz) {
            $obj = new SingleChoice();
            $obj->setBiz($biz);

            return $obj;
        };
        $container['question_type.uncertain_choice'] = function ($biz) {
            $obj = new UncertainChoice();
            $obj->setBiz($biz);

            return $obj;
        };
        $container['question_type.determine'] = function ($biz) {
            $obj = new Determine();
            $obj->setBiz($biz);

            return $obj;
        };
        $container['question_type.essay'] = function ($biz) {
            $obj = new Essay();
            $obj->setBiz($biz);

            return $obj;
        };
        $container['question_type.fill'] = function ($biz) {
            $obj = new Fill();
            $obj->setBiz($biz);

            return $obj;
        };
        $container['question_type.material'] = function ($biz) {
            $obj = new Material();
            $obj->setBiz($biz);

            return $obj;
        };

        $container['testpaper_pattern.questionType'] = function ($container) {
            return new QuestionTypePattern($container);
        };
    }
}
