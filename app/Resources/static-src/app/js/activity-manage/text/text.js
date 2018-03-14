import { initEditor } from '../editor';
import 'store';
export default class Text {
  constructor(props) {
    this._init();
  }

  _init() {
    this._inItStep2form();
    this._inItStep3form();
    this._lanuchAutoSave();

    $('.js-continue-edit').on('click', (event) => {
      const $btn = $(event.currentTarget);
      const content = $btn.data('content');
      this.editor.setData(content);
      $btn.remove();
    });
  }

  _inItStep2form() {
    const $step2_form = $('#step2-form');
    let validator = $step2_form.data('validator');
    validator = $step2_form.validate({
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        content: {
          required: true,
          trim: true,
        },
      },
    });
    const $content = $('[name="content"]');
    this.editor = initEditor($content, validator);
    this._contentCache = $content.val();
  }

  _lanuchAutoSave() {
    const $title = $('#modal .modal-title', parent.document);
    this._originTitle = $title.text();
    setInterval(() => {
      this._saveDraft();
    }, 5000);
  }

  _saveDraft() {
    const content = this.editor.getData();
    const needSave = content !== this._contentCache;
    if (!needSave) {
      return;
    }
    const $content = $('[name="content"]');
    $.post($content.data('saveDraftUrl'), { content: content })
      .done(() => {
        const date = new Date(); //日期对象
        const $title = $('#modal .modal-title', parent.document);
        const now = Translator.trans('site.date_format_his', {'hours': date.getHours(), 'minutes': date.getMinutes(), 'seconds': date.getSeconds()});
        $title.text(this._originTitle + Translator.trans('activity.text_manage.save_draft_hint', { createdTime: now }));
        this._contentCache = content;
      });
  }

  _inItStep3form() {
    const $step3_form = $('#step3-form');
    let validator = $step3_form.data('validator');
    validator = $step3_form.validate({
      rules: {
        finishDetail: {
          required: true,
          positive_integer: true,
          max: 300,
        },
      },
      messages: {
        finishDetail: {
          required: Translator.trans('activity.text_manage.finish_detail_required_error_hint'),
        },
      },
    });
  }
}