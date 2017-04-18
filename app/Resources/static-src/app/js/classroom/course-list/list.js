import {chapterAnimate} from 'app/common/widget/chapter-animate';

export default class CourseList {
  constructor($element) {
    this.$element = $element;
    chapterAnimate();
    this.initEvent();
    echo.init();
  }

  initEvent() {
    this.$element.on('click','.es-icon-keyboardarrowdown',(event)=>this.onExpandCourse(event));
    this.$element.on('click','.es-icon-keyboardarrowup',(event)=>this.onCollapseCourse(event));
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
}