import OpenCoursePlayer from './open-course-player';
import swfobject from 'es-swfobject';

if ($('#firstLesson').length > 0) {
  if (!swfobject.hasFlashPlayerVersion('11')) {
    const html = `
    <div class="alert alert-warning alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
      </button>
      ${Translator.trans('site.flash_not_install_hint')}
    </div>`;
    $('#lesson-preview-swf-player').html(html).show();
  } else {
    let openCoursePlayer = new OpenCoursePlayer({
      url: $('#firstLesson').data('url'),
      element: '.open-course-views',
    });
  }
}
