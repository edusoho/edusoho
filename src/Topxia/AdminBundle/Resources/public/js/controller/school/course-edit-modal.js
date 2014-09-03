define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    
    require('jquery.select2-css');
    require('jquery.select2');
    exports.run = function() {


        var $modal = $('#class-course-edit-form').parents('.modal');
        var $form = $("#class-course-edit-form");

        var validator = new Validator({
            element: '#class-course-edit-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $('#class-course-edit-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('课程修改成功');
                }).error(function(result){
                    Notify.danger('课程修改失败');
                });

            }
        });

        $('#teacherId').select2({
            ajax: {
                url: $('#teacherId').data('url') + '#',
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
            placeholder: "选择任课老师",
            createSearchChoice: function() {
                return null;
            }
        });

    };
});