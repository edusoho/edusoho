define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    require('jquery.select2-css');
    require('jquery.select2');

    exports.run = function() {
        var $modal = $('#number-form').parents('.modal');
        var validator = new Validator({
            element: '#number-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    if(html==true){
                        $modal.modal('hide');
                        Notify.success('添加管理员成功');
                        window.location.reload();
                    }else{
                        Notify.danger(html);
                    }
                }).error(function(){
                    Notify.danger('添加管理员失败');
                });

            }
        });

        $('#teacherId').select2({
            ajax: {
                url: $("#teacherId").data('url') + '#',
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
            placeholder: "选择老师",
            createSearchChoice: function() {
                return null;
            }
        });

        validator.addItem({
            element: '#teacherId',
            required: true,
            errormessage:'请选择老师'
        });

    };

});