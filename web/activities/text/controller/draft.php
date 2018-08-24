<?php

$container = \AppBundle\Component\Activity\ActivityRuntimeContainer::instance();

$request = $container->getRequest();

$content = $request->request->get('content', '');

if (empty($content)) {
    return $container->createJsonResponse(true);
}

$activityProxy = $container->getActivityProxy();
$activity = $activityProxy->getActivityContext()->getActivity();
$user = $activityProxy->getActivityContext()->getUser();

/** @var \Biz\Course\Service\CourseDraftService $courseDraftService */
$courseDraftService = $container->createService('Course:CourseDraftService');
$draft = $courseDraftService->getCourseDraftByCourseIdAndActivityIdAndUserId($activity['fromCourseId'], $activity['id'],
    $user['id']);

if (empty($draft)) {
    $draft = array(
        'activityId' => $activity['id'],
        'title' => '',
        'content' => $content,
        'courseId' => $activity['fromCourseId'],
    );

    $courseDraftService->createCourseDraft($draft);
} else {
    $draft['content'] = $content;
    $courseDraftService->updateCourseDraft($draft['id'], $draft);
}

return $container->createJsonResponse(true);

