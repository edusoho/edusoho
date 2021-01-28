define(function(require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function() {
    var $form = $('#org-manage-form');
    var $modal = $form.parents('.modal');

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return;
        }
        $('#org-create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize(), function(html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin.org_manage.save_success_hint'));
          window.location.reload();
        }).fail(function() {
          Notify.danger(Translator.trans('admin.org_manage.add_org_fail_hint'));
        });

      }
    });

    validator.addItem({
      element: '#org-name-field',
      required: true,
      rule: 'chinese_english byte_maxlength{max:30} remote'
    });

    validator.addItem({
      element: '#org-code-field',
      required: true,
      rule: 'alpha_numeric not_all_digital remote maxlength{max:30}'
    });


    Validator.addRule("chinese_english", /^([\u4E00-\uFA29]|[a-zA-Z ])*$/i, Translator.trans('validate_old.chinese_english.message', {display:'{{display}}'}));
    Validator.addRule("alpha_numeric", /^[a-zA-Z0-9]+$/i, Translator.trans('validate_old.alpha_numeric.message', {display:'{{display}}'}));

    $modal.find('.delete-org').on('click', function() {
      if (confirm(Translator.trans('admin.org_manage.delete_hint'))) {
        $.post($(this).data('url'), function(response) {
          if (response && response.status == 'error') {
            var msg = Translator.trans('admin.org_manage.contains_data.delete_fail_hint');
            $.each(response.data, function($key) {
              msg += $key + ' : ' + response.data[$key] + Translator.trans('admin.org_manage.contains_data_num') + "\t";
            });
            msg += Translator.trans('admin.org_manage.contains_data.delete_hint');
            Notify.danger(msg, 8);
            return false;
          }
          Notify.success(Translator.trans('admin.org_manage.delete_success_hint'));


          $modal.modal('hide');
          window.location.reload();
        });
      }
    });
  }

});