<?php

function _u($uri)
{
    return '/api' . $uri;
}

/**
 * @todo  待重构成新的路由配置方式
 */
$app->mount(_u('/users'), include dirname(__DIR__) . '/src/users.php' );
$app->mount(_u('/me'), include dirname(__DIR__) . '/src/me.php' );
$app->mount(_u('/courses'), include dirname(__DIR__) . '/src/courses.php' );
$app->mount(_u('/announcements'), include dirname(__DIR__) . '/src/announcements.php' );
$app->mount(_u('/coursethreads'), include dirname(__DIR__) . '/src/coursethreads.php' );
$app->mount(_u('/mobileschools'), include dirname(__DIR__) . '/src/mobileschools.php' );
$app->mount(_u('/blacklists'), include dirname(__DIR__) . '/src/blacklists.php' );
$app->mount(_u('/messages'), include dirname(__DIR__) . '/src/messages.php' );
$app->mount(_u('/files'), include dirname(__DIR__) . '/src/files.php' );


/**
 * 新的路由配置方式
 */
$app->get(_u('/articles'), 'res.Articles:get');
$app->get(_u('/articles/{id}'), 'res.Article:get');
$app->get(_u('/article_categories'), 'res.ArticleCategories:get');

$app->get(_u('/classrooms'), 'res.Classrooms:get');
$app->post(_u('/classrooms'), 'res.Classrooms:post');
$app->get(_u('/classrooms/{id}'), 'res.Classroom:get');
$app->post(_u('/classrooms/{id}'), 'res.Classroom:post');

$app->get(_u('/classrooms/{classroomId}/members'), 'res.ClassroomMembers:get');
$app->get(_u('/classrooms/{classroomId}/members/{memberId}'), 'res.ClassroomMember:get');

$app->get(_u('/me/chatrooms'), 'res.MeChatroomes:get');

$app->get(_u('/mobileschools/apps'), 'res.Apps:get');
$app->get(_u('/mobileschools/app/{id}'), 'res.App:get');