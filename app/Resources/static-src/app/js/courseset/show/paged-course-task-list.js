import PagedCourseLesson from 'app/js/courseset/show/paged-course-lesson';
import ESInfiniteCachedScroll from 'common/es-infinite-cached-scroll';

/**
 * render task list by js
 * @param options
 * {
 *   target: $dom //parent dom to render task list, default: $('.js-tasks-show')
 * }
 */
export default class PagedCourseTaskList extends PagedCourseLesson {
  _init(options) {
    if ($('input[name="showOptional"]').length > 0) {
      (window.localStorage.getItem('showOptional') == 'true') ? $('input[name="showOptional"]').attr('checked', 'checked') : '';
      $('input[name="showOptional"]').change(() => {
        window.localStorage.setItem('showOptional', $('input[name="showOptional"]').is(':checked'));
        window.location.reload();
      });
    }

    let $target = options.target ? options.target.find('.js-tasks-show') :$('.js-tasks-show') ;

    if ($target.length) {
      let query = ($('input[name="showOptional"]').length > 0) ? {showOptional: window.localStorage.getItem('showOptional')} : {};
      $.get($target.data('url'), query, (html) => {
        $target.html(html);
        let finalOptions = $.extend(this._getDefaultOptions(options), options);
        finalOptions.wrapDom = options.wrapTarget;
        finalOptions.pageSize = this._getPageSizeByMaxLessonsNumOfChapter(finalOptions)

        new ESInfiniteCachedScroll(finalOptions);

        if (this._displayAllImmediately) {
          this._destroyPaging();
        }

        if ($('.course-tasks-show-more').length < 1) {
          return;
        }

        if (finalOptions.data.length && finalOptions.data.length > 25) {
          $('.course-tasks-show-more').removeClass('hidden');
        }
      });
    }
  }


}