<?php

function _u($uri)
{
    return '/api'.$uri;
}

/**
 * @todo  待重构成新的路由配置方式
 */
$app->mount(_u('/users'), include dirname(__DIR__).'/src/users.php');
$app->mount(_u('/me'), include dirname(__DIR__).'/src/me.php');
$app->mount(_u('/courses'), include dirname(__DIR__).'/src/courses.php');
$app->mount(_u('/announcements'), include dirname(__DIR__).'/src/announcements.php');
$app->mount(_u('/coursethreads'), include dirname(__DIR__).'/src/coursethreads.php');
$app->mount(_u('/mobileschools'), include dirname(__DIR__).'/src/mobileschools.php');
$app->mount(_u('/blacklists'), include dirname(__DIR__).'/src/blacklists.php');
$app->mount(_u('/messages'), include dirname(__DIR__).'/src/messages.php');
$app->mount(_u('/files'), include dirname(__DIR__).'/src/files.php');

/**
 * 新的路由配置方式
 */
$app->get(_u('/user/{id}'), 'res.User:get');
$app->get(_u('/users'), 'res.Users:get');
$app->post(_u('/users'), 'res.Users:post');
$app->post(_u('/users/password'), 'res.User/Password:post');
$app->post(_u('/users/verified_mobile'), 'res.User/VerifiedMobile:post');

$app->get(_u('/courses'), 'res.Courses:get');
$app->get(_u('/courses/discovery/columns'), 'res.Courses:discoveryColumn');
$app->get(_u('/lessons'), 'res.Course/Lessons:get');
$app->get(_u('/course/{courseId}/lessons'), 'res.Course/LessonsToBeDelete:get'); //todo delete
$app->get(_u('/lessons/{id}'), 'res.Course/Lesson:get')->bind('get_lesson');
$app->get(_u('/threads'), 'res.Threads:get');
$app->get(_u('/chaos_threads'), 'res.ChaosThreads:get');
$app->post(_u('/chaos_threads'), 'res.ChaosThreads:post');
$app->get(_u('/chaos_threads/getThreads'), 'res.ChaosThreads:getThreads');
$app->post(_u('/chaos_threads_posts'), 'res.ChaosThreadsPosts:post');
$app->get(_u('/chaos_threads_posts/getThreadPosts'), 'res.ChaosThreadsPosts:getThreadPosts');
$app->get(_u('/articles'), 'res.Articles:get');
$app->get(_u('/articles/{id}'), 'res.Article:get');
$app->get(_u('/article_categories'), 'res.ArticleCategories:get');
$app->get(_u('/open_courses'), 'res.OpenCourses:get');
$app->get(_u('/open_course_lessons'), 'res.OpenCourse/Lessons:get');

$app->post(_u('/lessons/{id}/live_tickets'), 'res.LessonLiveTickets:post');
$app->get(_u('/lessons/{id}/live_tickets/{ticket}'), 'res.LessonLiveTicket:get');
$app->get(_u('/lessons/{id}/replay'), 'res.LessonReplay:get');

$app->get(_u('/courses/{courseId}/members'), 'res.Course/Members:get');
$app->get(_u('/courses/{courseId}/membership/{userId}'), 'res.CourseMembership:get');
$app->get(_u('/course/{courseId}/status'), 'res.Status:get');
$app->get(_u('/course_members'), 'res.CourseMembers:get');

$app->get(_u('/courses/{courseId}/notes/{noteId}'), 'res.Course/Note:get');
$app->get(_u('/courses/{courseId}/notes'), 'res.Course/Notes:get');
$app->post(_u('/courses/{courseId}/notes'), 'res.Course/Notes:post');

$app->get(_u('/courses/{courseId}/reviews'), 'res.CourseReviews:get');
$app->post(_u('/courses/{courseId}/reviews'), 'res.CourseReviews:post');

$app->get(_u('/classrooms'), 'res.Classrooms:get');
$app->post(_u('/classrooms'), 'res.Classrooms:post');
$app->get(_u('/classrooms/discovery/columns'), 'res.Classrooms:discoveryColumn');
$app->get(_u('/classrooms/{id}'), 'res.Classroom:get');
$app->post(_u('/classrooms/{id}'), 'res.Classroom:post');

$app->get(_u('/classrooms/{classroomId}/status'), 'res.ClassroomStatuses:get');

$app->get(_u('/classrooms/{classroomId}/members'), 'res.Classroom/Members:get');
$app->get(_u('/classrooms/{classroomId}/members/{memberId}'), 'res.Classroom/Member:get');
$app->get(_u('/classroom_members'), 'res.ClassroomMembers:get');

$app->get(_u('/exercise/{id}'), 'res.Exercise:get');
$app->get(_u('/exercise/{id}/result'), 'res.Exercise:result');
$app->post(_u('/exercise_results/{exerciseId}'), 'res.ExerciseResult:post');
$app->get(_u('/exercise_results/{lessonId}'), 'res.ExerciseResult:get');

$app->get(_u('/me/chatrooms'), 'res.MeChatroomes:get');
$app->get(_u('/me/courses'), 'res.MeCourses:get');

$app->get(_u('/mobileschools/apps'), 'res.Apps:get');
$app->get(_u('/mobileschools/app/{id}'), 'res.App:get');

$app->get(_u('/homework/{id}'), 'res.Homework:get');
$app->get(_u('/homework/{id}/result'), 'res.Homework:result');
$app->post(_u('/homework_results/{homeworkId}'), 'res.HomeworkResult:post');
$app->get(_u('/homework_results/{lessonId}'), 'res.HomeworkResult:get');

$app->post(_u('/upload/{group}'), 'res.Upload:post');

$app->get(_u('/analysis/{type}/{tab}'), 'res.Analysis:get');

$app->get(_u('/homework/manager/teaching'), 'res.HomeworkManager:teaching');
$app->get(_u('/thread/manager/question'), 'res.ThreadManager:question');
$app->get(_u('/homework/manager/check/{homeworkResultId}'), 'res.HomeworkManager:check');

$app->post(_u('/thread/create'), 'res.Thread:create');

$app->get(_u('/discovery_columns'), 'res.DiscoveryColumn:get');

$app->post(_u('/im/me/login'), 'res.IM/MeLogin:post');
$app->post(_u('/im/conversations'), 'res.IM/Conversations:post');
$app->post(_u('/im/members'), 'res.IM/Member:post');
$app->post(_u('/im/sync'), 'res.IM/MemberSync:post');
$app->post(_u('/im/me/push'), 'res.IM/MePush:post');

$app->get(_u('/courses/{courseId}/threads'), 'res.CourseThreads:get');
$app->get(_u('/courses/{courseId}/threads/{threadId}'), 'res.CourseThread:get');
$app->get(_u('/courses/{courseId}/threads/{threadId}/posts'), 'res.CourseThreadPosts:get');
$app->get(_u('/courses/{courseId}/threads/{threadId}/posts/{postId}'), 'res.CourseThreadPost:get');

$app->get(_u('/classroom_play/{classRoomId}'), 'res.ClassRoomPlay:get');
$app->get(_u('/classroom_play/{classRoomId}/status'), 'res.ClassRoomPlayStatus:get');

$app->get(_u('/classrooms/{classRoomId}/threads'), 'res.ClassRoomThreads:get');

$app->get(_u('/thread/{threadId}/posts'), 'res.ThreadPosts:get');
$app->get(_u('/classroom/thread/{threadId}'), 'res.ClassRoomThread:get');
$app->get(_u('/setting/{settingName}'), 'res.Setting:get');

$app->post(_u('/emails'), 'res.Emails:post');
$app->post(_u('/sms_codes'), 'res.SmsCodes:post');

$app->get(_u('/me/friends'), 'res.MeFriends:get');
$app->get(_u('/courses/{id}'), 'res.Course:get');
$app->get(_u('/my/learning'), 'res.MyLearning:get');
$app->get(_u('/courses_learn_progress'), 'res.CoursesLearnProgress:get');
$app->post(_u('/lesson/watch_time'), 'res.LessonWatchTime:post');
