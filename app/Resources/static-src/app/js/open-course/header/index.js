import OpenCoursePlayer from './open-course-player';
import swfobject from 'es-swfobject';
import { isMobileDevice } from 'common/utils';

if ($('#firstLesson').length > 0) {
  let openCoursePlayer = new OpenCoursePlayer({
    url: $('#firstLesson').data('url'),
    element: '.open-course-views',
  });
}
