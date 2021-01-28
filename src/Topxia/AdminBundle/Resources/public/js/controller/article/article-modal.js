define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Uploader = require('upload');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);
  require('jquery.select2-css');
  require('jquery.select2');
  require('es-ckeditor');

  require('../attachment/upload-form.js').run();

  exports.run = function() {
    var $form = $('#article-form');
    var $modal = $form.parents('.modal');
    var validator = _initValidator($form, $modal);
    var ckeditor = _initEditorFields($form, validator);

    validator.on('formValidate', function(elemetn, event) {
      ckeditor.updateElement();
    });

    _initSelect($form);

  };

  $('#article-property-tips').popover({
    html: true,
    trigger: 'hover',
    placement: 'left',
    content: $('#article-property-tips-html').html()
  });

  function _initSelect($form) {
    $('#article-tags').select2({

      ajax: {
        url: $('#article-tags').data('matchUrl'),
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
              id: item.name,
              name: item.name
            });
          });

          return {
            results: results
          };

        }
      },
      initSelection: function(element, callback) {
        var data = [];
        $(element.val().split(',')).each(function() {
          data.push({
            id: this,
            name: this
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
      placeholder: Translator.trans('validate.tag_required_hint'),
      width: 'off',
      createSearchChoice: function() {
        return null;
      }
    });

    $('#categoryId').select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholder: Translator.trans('admin.article_setting.choose_category'),
    });
  }

  function _initEditorFields($form, validator) {

    // group: 'default'
    var ckeditor = CKEDITOR.replace('richeditor-body-field', {
      toolbar: 'Admin',
      allowedContent: true,
      filebrowserImageUploadUrl: $('#richeditor-body-field').data('imageUploadUrl'),
      filebrowserFlashUploadUrl: $('#richeditor-body-field').data('flashUploadUrl'),
      height: 300
    });

    $('#article_thumb_remove').on('click', function() {
      if (!confirm(Translator.trans('site.delete.confirm_hint'))) return false;
      var $btn = $(this);
      $.post($btn.data('url'), function() {
        $('#article-thumb-container').html('');
        $form.find('[name=thumb]').val('');
        $form.find('[name=originalThumb]').val('');
        $btn.hide();
        Notify.success(Translator.trans('site.delete_success_hint'));
      }).error(function() {
        Notify.danger(Translator.trans('site.delete_fail_hint'));
      });
    });

    return ckeditor;
  }

  function _initValidator($form, $modal) {
    var validator = new Validator({
      element: '#article-form',
      failSilently: true,
      triggerType: 'change',
      onFormValidated: function(error, results, $form) {
        if (error) {
          return false;
        }
        $('#article-operate-save').button('loading').addClass('disabled');
        Notify.success(Translator.trans('admin.article.save.success'));
      }
    });

    validator.addItem({
      element: '[name=title]',
      required: true,
      rule: 'maxlength{max:255} visible_character'
    });

    validator.addItem({
      element: '[name=body]',
      required: true,
      rule: 'editor_maxlength{max:20000}',
    });

    validator.addItem({
      element: '[name=categoryId]',
      required: true,
      errormessageRequired: Translator.trans('admin.article.choose_column_tip'),
    });

    validator.addItem({
      element: '[name=sourceUrl]',
      rule: 'url'
    });

    return validator;
  }
});