define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
    require('jquery.select2-css');
    require('jquery.select2');
    exports.run = function() {
        var $form = $('#carousel-form');
        var $modal = $form.parents('.modal');

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#carousel-edit-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(){
                    Notify.success('课程轮播设置更新成功！');
                    $modal.modal('hide');
                    window.location.reload();
                });

            }
        });

        validator.addItem({
            element: '[name="name"]',
            required: true
        });

        validator.addItem({
            element: '[name="seq"]',
            required: true,
            rule: 'integer min{min: 0} max{max: 100}'
        });

        $('#columnId').select2({
            ajax: {
                url: $("#columnId").data('url') + '#',
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
                data['id'] = element.data('id');
                data['name'] = element.data('name');
                element.val(element.data('id'));
                callback(data);
            },
            formatSelection: function(item) {
                return item.name;
            },
            formatResult: function(item) {
                return item.name;
            },
            width: 'off',
            multiple: false,
            placeholder: "选择专栏",
            createSearchChoice: function() {
                return null;
            }
        });

    };




});