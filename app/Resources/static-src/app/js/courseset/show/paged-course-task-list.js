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

        this.cachedScroll = new ESInfiniteCachedScroll(finalOptions);

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

      $('body').on('click','.js-lesson-item-notify', function () {
        let $this = $(this);
        cd.message({type: 'warning', message: $this.data('notifyMessage')});
      });

      $(document).on('click', '.js-modal-only-app', function (event) {
        const $this = $(this);
        
        event.preventDefault();

        $this.attr('href', '#modal');
        $this.attr('data-toggle', 'modal');

        const currentURL = window.location.href;
        const matchIndex = currentURL.lastIndexOf('course/');
        let courseId = currentURL.substring(matchIndex + 7);
        if (matchIndex < 0) {
          const parentElement = $(this).closest('.js-tasks-show');

          // 检查是否找到了父级元素
          if (parentElement.length > 0) {
            // 获取父级元素的 data-url 属性的值
            const dataUrl = parentElement.data('url');
            const match = dataUrl.match(/\/course\/(\d+)\/task\/list\/render\/default/);

            // 检查是否找到匹配的数字部分
            if (match !== null && match.length > 1) {
              courseId = match[1];
            }
          }
        }

        $('#modal').load('/course/task/downloadAppShow?courseId='+courseId , () => {
          console.log('Load completed');
        });
        $('#modal').modal('show');
      });
    }
  }

  _destroy() {
    this.cachedScroll._destroy();
  }


}