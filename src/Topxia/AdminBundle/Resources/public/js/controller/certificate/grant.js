define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('jquery.bootstrap-datetimepicker');
  require('common/validator-rules').inject(Validator);
  exports.run = function() {


    var $form = $('#grant-form');

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return false;
        }

        let $modal = $('#modal');
        $('#grant-certificate').button('loading').addClass('disabled');
        $.post($form.attr('action'), $form.serialize(), function(response) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin_v2.certificate.record.grant.success_hint'));
          window.location.reload();
        }).error(function(){
          Notify.danger(Translator.trans('admin_v2.certificate.record.grant.failure_hint'));
        });
      }
    });

    validator.addItem({
      element: '[name="issueTime"]',
      required: true,
      errormessageRequired: Translator.trans('admin_v2.certificate.record.grant.issue_time_required'),
    });

    $('#issueTime').datetimepicker({
      format: 'yyyy-mm-dd',
      language: document.documentElement.lang,
      minView: 2,
      autoclose: true,
      startView: 2,
    });

    if ($('.js-loading-text').length>0) {
      $.post($('.js-loading-text').data('url'), (resp) => {
        let html = '<img class="mll" src="data:image/png;base64,'+ resp +'" width="520px" />';
        $('.js-loading-text').remove();
        $('.js-certificate-image').html(html);
      });
    }

  };
});