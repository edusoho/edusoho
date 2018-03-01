import OpenCoursePlayer from './open-course-player';

if ($('#firstLesson').length > 0) {
	let openCoursePlayer = new OpenCoursePlayer({
		url: $('#firstLesson').data('url'),
		element: '.open-course-views',
	});
}
