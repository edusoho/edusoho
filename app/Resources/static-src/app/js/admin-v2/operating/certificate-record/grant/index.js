import notify from 'common/notify';

export default class ReGrant {
  constructor(element) {
    this.$element = $(element);
    this.init();
  }

  init() {
    this.initDateTimePicker();
    this.initValidator();
    this.initEvent();
  }

  initEvent() {
    if ($('.js-loading-text').length>0) {
      $.post($('.js-loading-text').data('url'), (resp) => {
        let html = '<img class="mll" src="data:image/png;base64,'+ resp +'" width="520px" />';
        $('.js-loading-text').remove();
        $('.js-certificate-image').html(html);
      });
    }

    $('#grant-certificate').on('click', () => {
      if (this.validator.form()) {
        let $modal = $('#modal');
        $('#grant-certificate').button('loading').addClass('disabled');
        $.post(this.$element.attr('action'), this.$element.serialize(), function(response) {
          $modal.modal('hide');
          notify('success', Translator.trans('admin_v2.certificate.record.grant.success_hint'));
          window.location.reload();
        }).error(function(){
          notify('error', Translator.trans('admin_v2.certificate.record.grant.failure_hint'));
        });
      }
    });
  }

  initValidator() {
    this.validator = this.$element.validate({
      rules: {
        issueTime: {
          required: true,
        },
      },
      messages: {
        issueTime: {
          required: Translator.trans('admin_v2.certificate.record.grant.issue_time_required'),
        },
      },
    });
  }

  initDateTimePicker() {
    $('#issueTime').datetimepicker({
      format: 'yyyy-mm-dd',
      language: document.documentElement.lang,
      minView: 2,
      autoclose: true,
      startView: 2,
    });
  }
}

new ReGrant('#grant-form');
