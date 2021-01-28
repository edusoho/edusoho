<?php

namespace Biz\Xapi;

use Biz\Xapi\Type\AskQuestionType;
use Biz\Xapi\Type\AudioListen;
use Biz\Xapi\Type\BookmarkedCourseType;
use Biz\Xapi\Type\DoExerciseType;
use Biz\Xapi\Type\DoHomeworkType;
use Biz\Xapi\Type\DoQuestionType;
use Biz\Xapi\Type\DoTestpaperType;
use Biz\Xapi\Type\FinishActivityType;
use Biz\Xapi\Type\LiveWatchType;
use Biz\Xapi\Type\PurchasedClassroomType;
use Biz\Xapi\Type\PurchasedCourseType;
use Biz\Xapi\Type\RatedClassroomType;
use Biz\Xapi\Type\RatedCourseType;
use Biz\Xapi\Type\SearchKeywordType;
use Biz\Xapi\Type\UserLoggedInType;
use Biz\Xapi\Type\UserRegisteredType;
use Biz\Xapi\Type\VideoWatchType;
use Biz\Xapi\Type\WriteNoteType;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class XapiServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $this->registerPushType($biz);
    }

    private function registerPushType(Container $biz)
    {
        $biz[sprintf('xapi.push.%s', AskQuestionType::TYPE)] = $biz->factory(function ($biz) {
            $type = new AskQuestionType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', DoExerciseType::TYPE)] = $biz->factory(function ($biz) {
            $type = new DoExerciseType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', DoHomeworkType::TYPE)] = $biz->factory(function ($biz) {
            $type = new DoHomeworkType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', DoTestpaperType::TYPE)] = $biz->factory(function ($biz) {
            $type = new DoTestpaperType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', DoQuestionType::TYPE)] = $biz->factory(function ($biz) {
            $type = new DoQuestionType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', FinishActivityType::TYPE)] = $biz->factory(function ($biz) {
            $type = new FinishActivityType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', VideoWatchType::TYPE)] = $biz->factory(function ($biz) {
            $type = new VideoWatchType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', SearchKeywordType::TYPE)] = $biz->factory(function ($biz) {
            $type = new SearchKeywordType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', LiveWatchType::TYPE)] = $biz->factory(function ($biz) {
            $type = new LiveWatchType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', WriteNoteType::TYPE)] = $biz->factory(function ($biz) {
            $type = new WriteNoteType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', AudioListen::TYPE)] = $biz->factory(function ($biz) {
            $type = new AudioListen();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', SearchKeywordType::TYPE)] = $biz->factory(function ($biz) {
            $type = new SearchKeywordType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', PurchasedClassroomType::TYPE)] = $biz->factory(function ($biz) {
            $type = new PurchasedClassroomType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', PurchasedCourseType::TYPE)] = $biz->factory(function ($biz) {
            $type = new PurchasedCourseType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', UserLoggedInType::TYPE)] = $biz->factory(function ($biz) {
            $type = new UserLoggedInType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', BookmarkedCourseType::TYPE)] = $biz->factory(function ($biz) {
            $type = new BookmarkedCourseType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', RatedCourseType::TYPE)] = $biz->factory(function ($biz) {
            $type = new RatedCourseType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', RatedClassroomType::TYPE)] = $biz->factory(function ($biz) {
            $type = new RatedClassroomType();
            $type->setBiz($biz);

            return $type;
        });

        $biz[sprintf('xapi.push.%s', UserRegisteredType::TYPE)] = $biz->factory(function ($biz) {
            $type = new UserRegisteredType();
            $type->setBiz($biz);

            return $type;
        });

        $biz['xapi.options'] = array(
            'version' => '1.0.0',
            'getway' => '',
        );
    }
}
