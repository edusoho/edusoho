<?php

namespace Biz;

use AppBundle\Component\Notification\WeChatTemplateMessage\Client;
use AppBundle\Component\RateLimit\EmailRateLimiter;
use Biz\Common\BizCaptcha;
use Biz\Common\BizSms;
use Biz\Course\Util\CourseRenderViewResolver;
use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\NormalStrategy;
use Biz\Task\Strategy\StrategyContext;
use Gregwar\Captcha\CaptchaBuilder;
use Pimple\Container;
use Biz\Common\HTMLHelper;
use Pimple\ServiceProviderInterface;
use Biz\File\FireWall\FireWallFactory;
use Biz\Importer\CourseMemberImporter;
use Biz\Importer\ClassroomMemberImporter;
use Biz\Testpaper\Builder\ExerciseBuilder;
use Biz\Testpaper\Builder\HomeworkBuilder;
use Biz\Testpaper\Builder\TestpaperBuilder;
use Biz\Article\Event\ArticleEventSubscriber;
use Biz\Testpaper\Pattern\QuestionTypePattern;
use Biz\Thread\Firewall\ArticleThreadFirewall;
use Biz\Thread\Firewall\ClassroomThreadFirewall;
use Biz\Thread\Firewall\OpenCourseThreadFirewall;
use Biz\Sms\SmsProcessor\LiveOpenLessonSmsProcessor;
use Biz\Classroom\Event\ClassroomThreadEventProcessor;
use Biz\OpenCourse\Event\OpenCourseThreadEventProcessor;
use Biz\Announcement\Processor\AnnouncementProcessorFactory;
use Biz\User\Register\RegisterFactory;
use Biz\User\Register\Impl\EmailRegistDecoderImpl;
use Biz\User\Register\Impl\MobileRegistDecoderImpl;
use Biz\User\Register\Impl\BinderRegistDecoderImpl;
use Biz\User\Register\Impl\DistributorRegistDecoderImpl;
use Biz\User\Register\Common\RegisterTypeToolkit;
use Biz\Distributor\Service\Impl\SyncUserServiceImpl;
use Biz\Distributor\Service\Impl\SyncOrderServiceImpl;
use AppBundle\Component\RateLimit\RegisterSmsRateLimiter;
use Biz\Common\BizDragCaptcha;
use AppBundle\Component\RateLimit\SmsRateLimiter;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;

class DefaultServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['html_helper'] = function ($biz) {
            return new HTMLHelper($biz);
        };

        $biz['testpaper_builder.testpaper'] = function ($biz) {
            return new TestpaperBuilder($biz);
        };

        $biz['file_fire_wall_factory'] = function ($biz) {
            return new FireWallFactory($biz);
        };

        $biz['testpaper_builder.homework'] = function ($biz) {
            return new HomeworkBuilder($biz);
        };

        $biz['testpaper_builder.exercise'] = function ($biz) {
            return new ExerciseBuilder($biz);
        };

        $biz['announcement_processor'] = function ($biz) {
            return new AnnouncementProcessorFactory($biz);
        };

        $biz['sms_processor.lesson'] = function ($biz) {
            return new LessonSmsProcessor($biz);
        };

        $biz['sms_processor.liveOpen'] = function ($biz) {
            return new LiveOpenLessonSmsProcessor($biz);
        };

        $biz['thread_firewall.article'] = function ($biz) {
            return new ArticleThreadFirewall();
        };

        $biz['thread_firewall.classroom'] = function ($biz) {
            return new ClassroomThreadFirewall();
        };

        $biz['thread_firewall.openCourse'] = function ($biz) {
            return new OpenCourseThreadFirewall();
        };

        $biz['testpaper_pattern.questionType'] = function ($biz) {
            return new QuestionTypePattern($biz);
        };

        $biz['thread_event_processor.classroom'] = function ($biz) {
            return new ClassroomThreadEventProcessor($biz);
        };

        $biz['thread_event_processor.openCourse'] = function ($biz) {
            return new OpenCourseThreadEventProcessor($biz);
        };

        $biz['thread_event_processor.article'] = function ($biz) {
            return new ArticleEventSubscriber($biz);
        };

        $biz['importer.course-member'] = function ($biz) {
            return new CourseMemberImporter($biz);
        };

        $biz['importer.classroom-member'] = function ($biz) {
            return new ClassroomMemberImporter($biz);
        };

        $biz['course.strategy_context'] = function ($biz) {
            return new StrategyContext($biz);
        };
        $biz['course.default_strategy'] = function ($biz) {
            return new DefaultStrategy($biz);
        };
        $biz['course.normal_strategy'] = function ($biz) {
            return new NormalStrategy($biz);
        };

        $biz['user.register.type.toolkit'] = function ($biz) {
            return new RegisterTypeToolkit();
        };

        $biz['user.register'] = function ($biz) {
            return new RegisterFactory($biz);
        };

        $biz['user.register.email'] = function ($biz) {
            return new EmailRegistDecoderImpl($biz);
        };

        $biz['user.register.mobile'] = function ($biz) {
            return new MobileRegistDecoderImpl($biz);
        };

        $biz['user.register.binder'] = function ($biz) {
            return new BinderRegistDecoderImpl($biz);
        };

        $biz['user.register.distributor'] = function ($biz) {
            return new DistributorRegistDecoderImpl($biz);
        };

        $biz['distributor.sync.user'] = function ($biz) {
            return new SyncUserServiceImpl($biz);
        };

        $biz['distributor.sync.order'] = function ($biz) {
            return new SyncOrderServiceImpl($biz);
        };

        $biz['biz_captcha'] = $biz->factory(function ($biz) {
            $bizCaptcha = new BizCaptcha();
            $bizCaptcha->setBiz($biz);
            $bizCaptcha->setCaptchaBuilder(new CaptchaBuilder());

            return $bizCaptcha;
        });

        $biz['biz_drag_captcha'] = $biz->factory(function ($biz) {
            $bizDragCaptcha = new BizDragCaptcha();
            $bizDragCaptcha->setBiz($biz);

            return $bizDragCaptcha;
        });

        $biz['biz_sms'] = function ($biz) {
            $bizSms = new BizSms();
            $bizSms->setBiz($biz);

            return $bizSms;
        };

        $biz['register_sms_rate_limiter'] = function ($biz) {
            return new RegisterSmsRateLimiter($biz);
        };

        $biz['sms_rate_limiter'] = function ($biz) {
            return new SmsRateLimiter($biz);
        };

        $biz['email_rate_limiter'] = function ($biz) {
            return new EmailRateLimiter($biz);
        };

        $biz['render_view_resolvers'] = function ($biz) {
            return array(
                new CourseRenderViewResolver($biz),
            );
        };

        $biz['template_extension.live'] = array(
            'course/header/header-for-guest' => 'live-course/header/header-for-guest.html.twig',
        );

        $biz['educloud.live_client'] = function ($biz) {
            return new EdusohoLiveClient($biz);
        };

        $biz['course.show_redirect'] = array(
            "\/(my\/)?course\/(\d)+/i",
            "\/course_set\/(\d)+\/manage\/(\S)+/i",
            "\/my\/teaching\/course_sets/",
        );

        $biz['wechat.template_message_client'] = function ($biz) {
            $setting = $biz->service('System:SettingService');
            $loginBind = $setting->get('login_bind', array());
            if (!empty($loginBind['weixinmob_enabled'])) {
                $client = new Client(array(
                    'key' => $loginBind['weixinmob_key'],
                    'secret' => $loginBind['weixinmob_secret'],
                ));
                $token = $client->getAccessToken();
                if (!empty($token)) {
                    $client->setAccessToken($token['access_token']);
                }

                return $client;
            }

            return null;
        };

        $biz['lock.flock.directory'] = function ($biz) {
            return $biz['run_dir'];
        };

        $biz['queue.connection.database'] = function ($biz) {
            return new DatabaseQueue('database', $biz);
        };
    }
}
