import { chapterAnimate } from 'app/common/widget/chapter-animate';
import PagedCourseLesson from 'app/js/courseset/show/paged-course-lesson';

export default class CourseList {
  constructor($element) {
    this.$element = $element;
    chapterAnimate();
    this.initEvent();
    echo.init();
  }

  initEvent() {
    this.$element.on('click', '.es-icon-keyboardarrowdown', (event) => this.onExpandCourse(event));
    this.$element.on('click', '.es-icon-keyboardarrowup', (event) => this.onCollapseCourse(event));
  }

  onExpandCourse(e) {
    var $target = $(e.currentTarget);
    var $parent = $target.parents('.course-item');
    var $lessonList = $target.parents('.media').siblings('.js-course-detail-content');
    if ($lessonList.length > 0) {
      this._lessonListShow($lessonList);
    } else {
      var self = this;
      $.get($target.data('lessonUrl'), { 'visibility': 0 }, function(html) {
        $parent.append(html);
        new PagedCourseLesson({ displayAllImmediately: true });
        self._lessonListShow($parent.siblings('.js-course-detail-content'));
      });
    }

    $target.addClass('es-icon-keyboardarrowup').removeClass('es-icon-keyboardarrowdown');
  }
  onCollapseCourse(e) {
    var $target = $(e.currentTarget);
    this._lessonListShow($target.parents('.media').siblings('.js-course-detail-content'));
    $target.addClass('es-icon-keyboardarrowdown').removeClass('es-icon-keyboardarrowup');
  }
  _lessonListShow($list) {
    if ($list.length > 0) {
      $list.animate({
        visibility: 'toggle',
        opacity: 'toggle',
        easing: 'linear'
      });
      $list.height();
    }

  }
}