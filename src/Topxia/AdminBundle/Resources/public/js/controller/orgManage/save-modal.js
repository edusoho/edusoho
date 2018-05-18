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
          Notify.success(Translator.trans('保存组织机构成功！'));
          window.location.reload();
        }).fail(function() {
          Notify.danger(Translator.trans('添加组织机构失败，请重试！'));
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


    Validator.addRule("chinese_english", /^([\u4E00-\uFA29]|[a-zA-Z ])*$/i, "{{display}}必须是中文字、英文字母组成");
    Validator.addRule("alpha_numeric", /^[a-zA-Z0-9]+$/i, "{{display}}必须是英文字母、数字组成");

    $modal.find('.delete-org').on('click', function() {
      if (confirm(Translator.trans('真的要删除该组织机构及其辖下组织机构吗？'))) {
        $.post($(this).data('url'), function(response) {
          if (response && response.status == 'error') {
            var msg = Translator.trans("该组织机构下含有数据 ");
            $.each(response.data, function($key) {
              msg += $key + ' : ' + response.data[$key] + Translator.trans('条') + "\t";
            });
            msg += Translator.trans('请先转移到其他组织机构再进行删除!');
            Notify.danger(msg, 8);
            return false;
          }
          Notify.success(Translator.trans('组织机构已删除'));


          $modal.modal('hide');
          window.location.reload();
        });
      }
    });
  }

});