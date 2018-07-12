import { publish } from 'app/common/widget/publish';

const info = {
  title: 'course_set.manage.publish_title',
  hint: 'course_set.manage.publish_hint',
  success: 'course_set.manage.publish_success_hint',
  fail: 'course_set.manage.publish_fail_hint'
};
export const publishCourseSet = () => {
  $('body').on('click', '.course-publish-btn', function(evt) {
    if (!confirm(Translator.trans('course_set.manage.publish_hint'))) {
      return;
    }
    $.post($(evt.target).data('url'), function(data) {
      if (data.success) {
        notify('success', Translator.trans('course_set.manage.publish_success_hint'));
        location.reload();
      } else {
        notify('danger',Translator.trans('course_set.manage.publish_fail_hint')+':' + Translator.trans(data.message), {delay:5000});
      }
    });
  });
};

const $originHeader = $('.js-origin-header');
const $originHeaderContent = $('.js-origin-header-content');
const $newHeader = $('.js-new-header');
$('.js-shrink-item').on('click', '.js-shrink-courseset', (event) => {
  const $target = $(event.currentTarget);
  $target.addClass('hidden');
  $originHeader.animate({ height: '40px' }, () => {
    $originHeaderContent.animate({ opacity: '0' }, 'fast');
    $newHeader.animate({ opacity: '1' }, 'fast');
  });
});

$newHeader.on('click', '.js-show-courseset', (event) => {
  const $target = $(event.currentTarget);
  $originHeader.animate({ height: '122px' }, () => {
    $originHeaderContent.animate({ opacity: '1' }, 'fast');
    $newHeader.animate({ opacity: '0' }, 'fast');
    $('.js-shrink-courseset').removeClass('hidden');
  });
});

publish('.js-course-publish-btn', info);