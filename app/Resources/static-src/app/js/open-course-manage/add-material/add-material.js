import FileChooser from 'app/js/file-chooser/file-choose';
import notify from 'common/notify';
import { isEmpty } from 'common/utils';
import { chooserUiOpen } from 'app/js/activity-manage/widget/chooser-ui';

export default class AddMaterial {
  constructor() {
    this.$form = $('#course-material-form');
    this.initForm();
    this.bindEvent();
    this.initFileChooser();
  }

  initForm() {
    const validator2 = this.$form.validate({
      currentDom: '.js-add-file-list',
      ajax: true,
      rules: {
        link: 'url',
        fileId: 'required'
      },
      messages: {
        link: Translator.trans('activity.download_manage.link_error_hint'),
        fileId: Translator.trans('activity.download_manage.materials_error_hint')
      },
      submitHandler(form) {
        const $form = $(form);
        const settings = this.settings;
        const $btn = $(settings.currentDom);
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
        $('#link').val('');
        $('#materials-error').remove();
        notify('danger', Translator.trans('activity.download_manage.materials_or_link_error_hint'));
      }
    });

    this.$form.data('validator', validator2);
  }

  bindEvent() {
    this.$form.on('click', '.js-btn-delete', (event) => this.deleteItem(event));
    this.$form.on('click', '.js-video-import', () => this.addLink());
  }

  deleteItem(event) {
    const $target = $(event.currentTarget);
    const $parent = $target.closest('li');

    if (!confirm(Translator.trans('activity.download_manage.materials_or_link_confirm_delete'))) {
      return;
    }

    $.post($target.data('url'), () => {
      $parent.remove();
      notify('success', Translator.trans('activity.download_manage.materials_or_link_delete'));
    });
  }

  addLink() {
    const $verifyLink = $('#verifyLink');
    const linkVal = $('#link').val();
    const $materials = $('#materials');
    const isValidated = this.$form.data('validator').valid();

    if (isValidated && linkVal) {
      $materials.val(0);
      $verifyLink.val(linkVal);
    } else {
      $('#link').val('');
      $verifyLink.val('');
      $materials.val('');
    }

    $('.js-current-file').text($verifyLink.val());
  }

  initFileChooser() {
    const fileSelect = (file) => {
      const $media = $('#media');
      const $materials = $('#materials');

      $media.val(JSON.stringify(file));
      chooserUiOpen();
      $('.js-current-file').text(file.name);
      let media = isEmpty($media.val()) ? Object.create(null) : JSON.parse($media.val());
      $materials.val(media.id);
    };

    const fileChooser = new FileChooser();
    fileChooser.on('select', fileSelect);
  }

}