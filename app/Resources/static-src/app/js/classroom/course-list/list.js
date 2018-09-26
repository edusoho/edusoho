import { chapterAnimate } from 'app/common/widget/chapter-animate';
import PagedCourseLesson from 'app/js/courseset/show/paged-course-lesson';

export default class CourseList {
  constructor($element) {
    this.$element = $element;
    chapterAnimate();
    this.initEvent();
  }

  initEvent() {
    this.$element.on('click', '.es-icon-keyboardarrowdown', (event) => this.onExpandCourse(event));
    this.$element.on('click', '.es-icon-keyboardarrowup', (event) => this.onCollapseCourse(event));
  }

  onExpandCourse(e) {
    const $target = $(e.currentTarget);
    const $parent = $target.parents('.course-item');
    const $lessonList = $target.parents('.media').siblings('.js-course-detail-content');
    if ($lessonList.length > 0) {
      this._lessonListShow($lessonList);
    } else {
      $.get($target.data('lessonUrl'), { 'visibility': 0 }, function(html) {
        $parent.append(html);
        new PagedCourseLesson({wrapTarget: $parent});
      });
    }
    const $hideDom = $parent.siblings().find('.es-icon-keyboardarrowup');
    this._lessonListShow($hideDom.parents('.media').siblings('.js-course-detail-content'));
    const $findAllLink = $parent.find('.js-all-courses-link');
    const $otherAllLink = $parent.siblings().find('.js-all-courses-link');
    if ($findAllLink.length) {
      $findAllLink.removeClass('hidden');
    }

    this.hideLink($otherAllLink);
    $hideDom.removeClass('es-icon-keyboardarrowup').addClass('es-icon-keyboardarrowdown');
    $target.addClass('es-icon-keyboardarrowup').removeClass('es-icon-keyboardarrowdown');
  }

  onCollapseCourse(e) {
    const $target = $(e.currentTarget);
    const $findAllLink = $target.parents('.course-item').find('.js-all-courses-link');
    this.hideLink($findAllLink);
    this._lessonListShow($target.parents('.media').siblings('.js-course-detail-content'));
    $target.addClass('es-icon-keyboardarrowdown').removeClass('es-icon-keyboardarrowup');
  }

  hideLink($dom) {
    if ($dom.length) {
      $dom.addClass('hidden');
    }
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