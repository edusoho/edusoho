define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('../widget/category-select').run('article');
  require('common/validator-rules').inject(Validator);
  require('jquery.select2-css');
  require('jquery.select2');

  exports.run = function() {
    var $form = $('#bank-form');
    var $modal = $form.parents('.modal');
    var validator = _initValidator($form, $modal);

    _initSelect($form);

  };

  function _initSelect($form) {
    $('#bank-members').select2({

      ajax: {
        url: $('#bank-members').data('matchUrl'),
        dataType: 'json',
        quietMillis: 100,
        data: function(term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results: function(data) {

          var results = [];

          $.each(data, function(index, item) {

            results.push({
              id: item.id,
              name: item.nickname
            });
          });

          return {
            results: results
          };

        }
      },
      initSelection: function(element, callback) {
        var data = [];
        var members =  JSON.parse(element.val());
        element.val('')
        $(members).each(function() {
          data.push({
            id: this.id,
            name: this.name
          });
        });
        callback(data);
      },
      formatSelection: function(item) {
        return item.name;
      },
      formatResult: function(item) {
        return item.name;
      },
      multiple: true,
      maximumSelectionSize: 20,
      width: 'off',
      createSearchChoice: function() {
        return null;
      }
    });
  }
  function _initValidator($form, $modal) {
    var validator = new Validator({
      element: '#bank-form',
      failSilently: true,
      triggerType: 'change',
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return ;
        }

        $('#create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize()).done(function(html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin.question_bank.save_success'));
          window.location.reload();
        }).fail(function() {
          Notify.danger(Translator.trans('admin.question_bank.save_fail'));
        });

      }
    });

    validator.addItem({
      element: '#bank-name',
      required: true,
      rule: 'maxlength{max:30}'
    });

    validator.addItem({
      element: '#bank-category',
      required: true,
      errormessageRequired: Translator.trans('admin.question_bank.choose_category'),
    });

    return validator;
  }
});