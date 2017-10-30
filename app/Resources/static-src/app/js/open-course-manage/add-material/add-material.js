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
        $.post($form.attr('action'), $form.serializeArray(), (data) => {
          notify('success', Translator.trans('activity.download_manage.materials_or_link_success'));
          $("#material-list").append(data);
          $btn.button('reset');
          $form.find('#materials').val('');
          $form.find('#link').val('');
          $form.find('#media').val('');
          $form.find('#file-summary').val('');
          $form.find('.js-current-file').text('');
        }).error((data) => {
          $btn.button('reset');
          notify('warning', Translator.trans('activity.download_manage.materials_or_link_fail'));
        });
      }
    });

    $('.js-add-file-list').click(() => {
      if (validator2.form()) {
        this.$form.submit();
      } else {
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

    $.post($target.data('url'), () => {
      $parent.remove();
      notify('success', Translator.trans('activity.download_manage.materials_or_link_delete'));
    });
  }

  addLink() {
    let $media = $("#media");
    let $materials = $("#materials");
    let $verifyLink = $("#verifyLink");
    let linkVal = $("#link").val();
    let linkLength = linkVal.length;

    let validateFlag = this.$form.data('validator').valid();

    if (validateFlag && (linkLength > 0)) {
      $verifyLink.val(linkVal);
      $materials.val(0);
    } else {
      $verifyLink.val('');
      $materials.val('');
    }
    $("#link").val($verifyLink.val());
    $('.js-current-file').text($verifyLink.val());
  }

  initFileChooser() {

    const fileSelect = file => {

      let $media = $("#media");
      let $materials = $("#materials");
      let media = null;
      let fileIdVal = null;

      $media.val(JSON.stringify(file));
      chooserUiOpen();
      $('.js-current-file').text(file.name);

      media = isEmpty($media.val()) ? {} : JSON.parse($media.val());
      fileIdVal = $materials.val(media.id);
    }

    const fileChooser = new FileChooser();

    fileChooser.on('select', fileSelect);
  }

}