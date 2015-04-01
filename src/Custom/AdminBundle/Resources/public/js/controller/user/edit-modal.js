define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require('ckeditor');
        require('jquery.select2-css');
    require('jquery.select2');
        
         exports.run = function() {
	          $('#course_tags').select2({

            ajax: {
                url: '/teacher/tag/match_jsonp' + '#',
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
                $(element.val().split(",")).each(function() {
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
            width: 'off',
            multiple: true,
            maximumSelectionSize: 20,
            placeholder: "请输入标签",
            width: 'off',
            multiple: true,
            createSearchChoice: function() {
                return null;
            },
            maximumSelectionSize: 20
        });

	         
	                 	        var editor = CKEDITOR.replace('about', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#about').data('imageUploadUrl')
        });

        var $modal = $('#user-edit-form').parents('.modal');

        var validator = new Validator({
            element: '#user-edit-form',
            autoSubmit: false,
             failSilently: true,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#edit-user-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('用户信息保存成功');
                    var $tr = $(html);
                    $('#' + $tr.attr('id')).replaceWith($tr);
                }).error(function(){
                    Notify.danger('操作失败');
                });
            }
        });

        validator.on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });


        validator.addItem({
            element: '[name="truename"]',
            rule: 'chinese minlength{min:2} maxlength{max:5}'
        });

        validator.addItem({
            element: '[name="qq"]',
            rule: 'qq'
        });

        validator.addItem({
            element: '[name="weibo"]',
            rule: 'url',
            errormessageUrl: '网站地址不正确，须以http://weibo.com开头。'
        });

        validator.addItem({
            element: '[name="site"]',
            rule: 'url',
            errormessageUrl: '网站地址不正确，须以http://开头。'
        });

        validator.addItem({
            element: '[name="mobile"]',
            rule: 'phone'
        });

        validator.addItem({
            element: '[name="idcard"]',
            rule: 'idcard'
        });

        for(var i=1;i<=5;i++){
             validator.addItem({
             element: '[name="intField'+i+'"]',
             rule: 'int'
             });

             validator.addItem({
            element: '[name="floatField'+i+'"]',
            rule: 'float'
            });

             validator.addItem({
            element: '[name="dateField'+i+'"]',
            rule: 'date'
             });
        }

        };

});