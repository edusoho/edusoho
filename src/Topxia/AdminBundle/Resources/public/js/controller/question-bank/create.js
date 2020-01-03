define(function(require, exports, module) {

  let Validator = require('bootstrap.validator');
  let Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);
  require('jquery.select2-css');
  require('jquery.select2');

  exports.run = function() {
    let $form = $('#bank-form');
    let $modal = $form.parents('.modal');

    _initValidator($form, $modal);
    _initSelect($form);
  };

  function _initSelect($form) {
    $('[name="categoryId"]').select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first',
      formatNoMatches: function() {
        return Translator.trans('admin.question_bank.no_category');
      }
    });

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
          let results = [];

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
        let data = [];
        let members =  JSON.parse(element.val());
        element.val('');
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
    let validator = new Validator({
      element: '#bank-form',
      failSilently: true,
      triggerType: 'change',
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return ;
        }

        $('#create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize()).done(function() {
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
      rule: 'maxlength{max:30} visible_character'
    });

    validator.addItem({
      element: '#bank-category',
      required: true,
      errormessageRequired: Translator.trans('admin.question_bank.choose_category'),
    });

    return validator;
  }
});