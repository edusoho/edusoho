export default class courseList {
  constructor($element) {
    this._initChapter();
  }

  initEvent() {

  }

  onExpandCourse(e) {
    var $target = $(e.currentTarget);
    var $parent = $target.parents(".course-item");
    var $lessonList = $target.parents(".media").siblings(".course-detail-content");
    if ($lessonList.length > 0) {
      this._lessonListSHow($lessonList)
    } else {
      var self = this;
      $.get($target.data('lessonUrl'), { 'visibility': 0 }, function (html) {
        $parent.append(html);
        self._initChapter();
        self._lessonListSHow($parent.siblings(".course-detail-content"));
      });
    }

    $target.addClass('es-icon-keyboardarrowup').removeClass('es-icon-keyboardarrowdown');
  }
  onCollapseCourse(e) {
    var $target = $(e.currentTarget);
    this._lessonListSHow($target.parents(".media").siblings(".course-detail-content"));
    $target.addClass('es-icon-keyboardarrowdown').removeClass('es-icon-keyboardarrowup');
  }
  _lessonListSHow($list) {
    if ($list.length > 0) {
      $list.animate({
        visibility: 'toggle',
        opacity: 'toggle',
        easing: 'linear'
      });
      $list.height();
    }

  }
  _initChapter(e) {
    $('body').on('click', '.js-task-chapter', function () {
      $(this).nextUntil(".js-task-chapter").animate({ height: 'toggle', opacity: 'toggle' }, "normal");
      let $icon = $(this).children('.js-remove-icon');
      if ($icon.hasClass('es-icon-remove')) {
        $icon.removeClass('es-icon-remove').addClass('es-icon-anonymous-iconfont');
      } else {
        $icon.removeClass('es-icon-anonymous-iconfont').addClass('es-icon-remove');
      }
    });
  }
}