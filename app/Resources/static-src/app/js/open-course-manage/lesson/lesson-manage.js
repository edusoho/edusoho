export default class LessonManage {
  constructor() {
    this.$item = $('.js-open-course-lesson-item');
    this.init();
  }

  init() {
    $('.js-file-delete-lesson').tooltip();
    this.asyncLoadFiles();
    this.bindEvent();
  }

  bindEvent() {
    this.$item.on('click', '.js-publish-lesson-btn', (event) => this.publishLesson(event));
    this.$item.on('click', '.js-unpublish-lesson-btn', (event) => this.cancelPublishLesson(event));
    this.$item.on('click', '.js-delete-lesson-btn', (event) => this.deleteLesson(event));
    $('.js-lesson-create-btn').on('click', (event) => this.createLesson(event));
  }

  publishLesson(event) {
    const $btn = $(event.target);
    const message = Translator.trans('open_course.publish_lesson_hint');
    this.togglePublish($btn, message);
  }

  cancelPublishLesson(event) {
    const $btn = $(event.target);
    const message = Translator.trans('open_course.unpublish_success_hint');
    this.togglePublish($btn, message);
  }

  togglePublish($target, message) {
    $.post($target.data('url'), (html) => {
      $('.js-publish-lesson-btn, .js-delete-lesson-btn, .js-unpublish-lesson-btn').parent().toggleClass('hidden');
      $('.js-unpublish-status').toggleClass('hidden');
      cd.message({ type: 'success', message: message });
    });
  }

  deleteLesson(event) {
    const $btn = $(event.target);
    cd.confirm({
      title: Translator.trans('site.delete'),
      content: Translator.trans('open_course.lesson_delete_hint'),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close')
    }).on('ok', () => {
      $.post($btn.data('url'), (response) => {
        this.$item.remove();
        $('.js-lesson-notify').show();
        $('.js-lesson-create-btn').attr('disabled', false);
        cd.message({ type: 'success', message: Translator.trans('open_course.lesson_delete_success_hint') });
      }, 'json');
    });
  }

  createLesson(event) {
    let url = $(event.target).data('url');
    $.get(url, (data) => {
      if (data['result']) {
        cd.message({ type: 'warning', message: Translator.trans('open_course.add_lesson_hint') });
      } else {
        $('#modal').html(data);
        $('#modal').modal('show');
      }
    });
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
        this.$item.find('span[data-role="mediaStatus"]').append('<span class=\'text-warning\'>'+Translator.trans('open_course.file_format_conversion_hint')+'</span>');
      } else if (file.convertStatus == 'error') {
        this.$item.find('span[data-role="mediaStatus"]').append('<span class=\'text-danger\'>'+Translator.trans('open_course.file_format_conversion_failed_hint')+'</span>');
      }
    });
  }
}
