import FileChooser from 'app/js/file-chooser/file-choose';
import notify from 'common/notify';
import {
  isEmpty
} from 'common/utils';
import {
  chooserUiOpen
} from 'app/js/activity-manage/widget/chooser-ui';

export default class AddMaterial {
  constructor() {
    this.$form = $('#course-material-form');
    this.validator2 = null;
    this.initForm();
    this.bindEvent();
    this.initFileChooser();
  }

  initForm() {
    let validator2 = this.$form.validate({
      currentDom: '.js-add-file-list',
      ajax: true,
      rules: {
        link: 'url',
        fileId: 'required'
      },
      messages: {
        link: Translator.trans("activity.download_manage.link_error_hint"),
        fileId: Translator.trans('activity.download_manage.materials_error_hint')
      },
      submitHandler(form) {
        let $form = $(form);
        let settings = this.settings;
        let $btn = $(settings.currentDom);
        $btn.button('loading');
        $.post($form.attr('action'), $form.serializeArray())
          .done((data) => {
            notify('success', Translator.trans('activity.download_manage.materials_or_link_success'));
            $('#material-list').append(data);
            $btn.button('reset');
            $form.find('#materials').val('');
            $form.find('#link').val('');
            $form.find('#verifyLink').val('');
            $form.find('#media').val('');
            $form.find('#file-summary').val('');
            $form.find('.js-current-file').text('');
          }).fail(() => {
            $btn.button('reset');
            notify('warning', Translator.trans('activity.download_manage.materials_or_link_fail'));
          });
      }
    });

    $('.js-add-file-list').click(() => {
      if (validator2.form()) {
        this.$form.submit();
      } else {
        // 移除底部错误提示，采用notify效果
        $('#materials-error').remove();
        notify('danger', Translator.trans('activity.download_manage.materials_or_link_error_hint'));
        this.$form.find('#materials').val('');
      }
    });

    this.$form.data('validator', validator2);
  }

  bindEvent() {
    this.$form.on('click', '.js-btn-delete', (event) => this.deleteItem(event));
    this.$form.on('click', '.js-video-import', () => this.addLink());
  }

  deleteItem(event) {
    let $target = $(event.currentTarget);
    let $parent = $target.closest('li');

    if (!confirm(Translator.trans('activity.download_manage.materials_or_link_confirm_delete'))) {
      return;
    }

    $.post($target.data('url'), () => {
      $parent.remove();
      notify('success', Translator.trans('activity.download_manage.materials_or_link_delete'));
    });
  }

  addLink() {
    const $materials = $('#materials');
    const $verifyLink = $('#verifyLink');
    let linkVal = $('#link').val();
    let linkLength = linkVal.length;
    let isValidated = this.$form.data('validator').valid();

    if (isValidated && (linkLength > 0)) {
      $verifyLink.val(linkVal);
      $materials.val(0);
    }

    $('#link').val($verifyLink.val());
    $('.js-current-file').text($verifyLink.val());
  }

  initFileChooser() {
    const fileSelect = (file) => {
      const $media = $('#media');
      const $materials = $('#materials');
      let media = {};

      $media.val(JSON.stringify(file));
      chooserUiOpen();
      $('.js-current-file').text(file.name);
      media = isEmpty($media.val()) ? {} : JSON.parse($media.val());
      $materials.val(media.id);
    }

    const fileChooser = new FileChooser();
    fileChooser.on('select', fileSelect);
  }

}