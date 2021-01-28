import { publish } from 'app/common/widget/publish';

export const closeCourse = () => {
  $('body').on('click', '.js-close-course', (evt) => {
    let $target = $(evt.currentTarget);
    cd.confirm({
      title: Translator.trans('site.close'),
      content: Translator.trans('course.manage.close_hint'),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.cancel')
    }).on('ok', () => {
      $.post($target.data('checkUrl'), (data) => {
        if (data.warn) {
          cd.confirm({
            title: Translator.trans('site.close'),
            content: Translator.trans(data.message),
            okText: Translator.trans('site.confirm'),
            cancelText: Translator.trans('site.cancel')
          }).on('ok', () => {
            closeCourseAction($target);
          });
          return;
        }
        closeCourseAction($target);
      });
    });
  });
};

const closeCourseAction = ($target) => {
  $.post($target.data('url'), (data) => {
    if (data.success) {
      cd.message({ type: 'success', message: Translator.trans('course.manage.close_success_hint') });
      location.reload();
    } else {
      cd.message({ type: 'danger', message: Translator.trans('course.manage.close_fail_hint') + ':' + data.message });
    }
  });
};

export const deleteCourse = () => {
  $('body').on('click', '.js-delete-course', (evt) =>  {
    cd.confirm({
      title: Translator.trans('site.delete'),
      content: Translator.trans('course.manage.delete_hint'),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.cancel')
    }).on('ok', () => {
      $.post($(evt.currentTarget).data('url'), (data) => {
        if (data.success) {
          cd.message({ type: 'success', message: Translator.trans('site.delete_success_hint') });
          if (data.redirect) {
            window.location.href = data.redirect;
          } else {
            location.reload();
          }
        } else {
          cd.message({ type: 'danger', message: Translator.trans('site.delete_fail_hint') + ':' + data.message });
        }
      });
    });
  });
};

export const publishCourse = () => {
  const info = {
    title: 'course.manage.publish_title',
    hint: 'course.manage.publish_hint',
    success: 'course.manage.publish_success_hint',
    fail: 'course.manage.publish_fail_hint'
  };

  publish('.js-publish-course', info);
};

export const showSettings = () => {
  $('#sortable-list').on('click', '.js-item-content', (event) => {
    console.log('click');
    let $this = $(event.currentTarget);
    let $li = $this.closest('.js-task-manage-item');
    if ($li.hasClass('active')) {
      $li.removeClass('active').find('.js-settings-list').stop().slideUp(500);
    }
    else {
      $li.addClass('active').find('.js-settings-list').stop().slideDown(500);
      $li.siblings('.js-task-manage-item.active').removeClass('active').find('.js-settings-list').hide();
    }
  });
};

export const TabChange = () => {
  $('[data-role="tab"]').click(function (event) {
    let $this = $(this);
    $($this.data('tab-content')).removeClass('hidden').siblings('[data-role="tab-content"]').addClass('hidden');
  });
};


export const TaskListHeaderFixed = () => {
  let $header = $('.js-task-list-header');
  if(!$header.length){
    return;
  }
  let headerTop = $header.offset().top;
  $(window).scroll(function(event) {
    if ($(window).scrollTop() >= headerTop) {
      $header.addClass('fixed');
    } else {
      $header.removeClass('fixed');
    }
  });
};
