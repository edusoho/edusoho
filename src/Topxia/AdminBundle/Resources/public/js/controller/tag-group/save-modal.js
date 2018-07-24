define(function(require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  require('jquery.select2-css');
  require('jquery.select2');

  exports.run = function() {
    var $form = $('#tag-group-form');
    var $modal = $form.parents('.modal');
    var $table = $('#tag-group-table');

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
            return ;
        }

        if ($('#s2id_tags').find('.select2-search-choice').length == 0) {
            Notify.danger(Translator.trans('admin.tag_group.select_tag_empty_hint'));
            return;
        }

        $('#tag-group-create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize(), function(html){
            var $html = $(html);
            if ($table.find( '#' +  $html.attr('id')).length > 0) {
                $('#' + $html.attr('id')).replaceWith($html);
                Notify.success(Translator.trans('admin.tag_group.update_success_hint'));
            } else {
                if ($('.empty')) {
                  $('.empty').remove();
                }
                $table.find('tbody').prepend(html);
                Notify.success(Translator.trans('admin.tag_group.add_success_hint'));
            }
            $modal.modal('hide');
        });
      }
    });

    validator.addItem({
      element: '#tag-group-name-field',
      required: true,
      rule: 'remote byte_maxlength{max:20}'
    });

    $modal.find('.delete-tag-group').on('click', function() {
    if (!confirm(Translator.trans('admin.tag_group.delete_hint'))) {
      return ;
    }

    var trId = '#tag-group-tr-' + $(this).data('tagGroupId');
    $.post($(this).data('url'), function(html) {
      $modal.modal('hide');
      $table.find(trId).remove();
    });

    });


    var $tagContainer = $('#tags');
    $tagContainer.select2({
        tags: true,
        ajax: {
            url: $tagContainer.data('url') + '#',
            dataType: 'json',
            quietMillis: 100,
            data: function (term, page) {
                return {
                    q: term,
                    page_limit: 10
                };
            },
            results: function (data) {
                var results = [];
                $.each(data, function (index, item) {

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
        initSelection: function (element, callback) {
            var data = [];
            $(element.val().split(",")).each(function () {
                data.push({
                    id: this,
                    name: this
                });
            });
            callback(data);
        },
        formatSelection: function (item) {
            return item.name;
        },
        formatResult: function (item) {
            return item.name;
        },
        width: 'off',
        maximumSelectionSize: 50,
        maximumInputLength: 10,
        placeholder: Translator.trans('admin.tag_group.tag_input_placeholder'),
        multiple: true,
        tokenSeparators: [",", " "],
        createSearchChoice: function(term,data) {
            return {id:term, name:term};
        }
    });
  };
});