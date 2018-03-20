import notify from 'common/notify';

export default class LessonManage {
  constructor() {
    this.$item = $('.js-open-course-lesson-item');
    this.init();
  }

  init() {
    $(".js-file-delete-lesson").tooltip();
    this.asyncLoadFiles();
    this.bindEvent();
  }

  bindEvent() {
    this.$item.on('click', '.js-publish-lesson-btn', (event) => this.publishLesson(event));
    this.$item.on('click', '.js-unpublish-lesson-btn', () => this.cancelPublishLesson(event));
    this.$item.on('click', '.js-delete-lesson-btn', () => this.deleteLesson(event));
    $('.js-lesson-create-btn').on('click', () => this.createLesson(event));
  }

  publishLesson(event) {
    let $btn = $(event.target);
    $.post($btn.data('url'), (html) => {
      $('.js-unpublish-status').remove();
      $('.js-publish-lesson-btn, .js-delete-lesson-btn').parent().addClass('hidden');
      $('.js-unpublish-lesson-btn').parent().removeClass('hidden');
      notify('success', Translator.trans('open_course.publish_lesson_hint'));
    });
  }

  cancelPublishLesson(event) {
    let $btn = $(event.target);
    $.post($btn.data('url'), (html) => {
      $('.js-item-content').prepend('<span class="lesson-unpublish-status js-unpublish-status">' + Translator.trans('open_course.unpublish_hint') +'</span>');
      $('.js-publish-lesson-btn, .js-delete-lesson-btn').parent().removeClass('hidden');
      $('.js-unpublish-lesson-btn').parent().addClass('hidden');
      notify('success', Translator.trans('open_course.unpublish_success_hint'));
    });
  }

  deleteLesson(event) {
    if (!confirm(Translator.trans('open_course.lesson_delete_hint'))) {
      return;
    }
    let $btn = $(event.target);
    $.post($btn.data('url'), (response) => {
      this.$item.remove();
      $('.js-lesson-notify').show();
      $('.js-lesson-create-btn').attr('disabled', false);
      notify('success', Translator.trans('open_course.lesson_delete_success_hint'));
    }, 'json');
  }

  createLesson(event) {
    let url = $(event.target).data('url');
    $.get(url, (data) => {
      if (data['result']) {
        notify('warning', Translator.trans('open_course.add_lesson_hint'));
      } else {
        $('#modal').html(data);
        $('#modal').modal('show');
      }
    })
  }

  asyncLoadFiles() {
    const url = $('.js-lesson-manage-panel').data('file-status-url');
    const id = this.$item.data('fileId');
    $.post(url, { 'ids': id }, (data) => {
      if (!data || data.length == 0) {
        return;
      }
      const file = data[0];
      if (file.convertStatus == 'waiting' || file.convertStatus == 'doing') {
        this.$item.find('span[data-role="mediaStatus"]').append("<span class='text-warning'>"+Translator.trans('open_course.file_format_conversion_hint')+"</span>");
      } else if (file.convertStatus == 'error') {
        this.$item.find('span[data-role="mediaStatus"]').append("<span class='text-danger'>"+Translator.trans('open_course.file_format_conversion_failed_hint')+"</span>");
      }
    });
  }
}
