<?php

namespace Topxia\Service\Course\Listener;

use Topxia\Service\Common\ServiceEventListener;
use Topxia\Service\Favorite\Event\FavoriteEvent;
use Topxia\Service\Favorite\Event\FavoriteEventListener;

class CourseFavoriteListener extends ServiceEventListener implements FavoriteEventListener {

    public function onFavoriteAdd(FavoriteEvent $event) {
        $courseId = $event->favorite['typeId'];
        if (empty($courseId)) {
            return ;
        }
        $this->getCourseService()->waveCourseFavoriteNum($courseId, 1);
    }

    public function onFavoriteRemove (FavoriteEvent $event) {
        $courseId = $event->favorite['typeId'];
        if (empty($courseId)) {
            return ;
        }
        $this->getCourseService()->waveCourseFavoriteNum($courseId, -1);
    }

    protected function getCourseService() {
        return $this->container->get('topxia.course_service');
    }

}