define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    var EditorFactory = require('common/kindeditor-factory');

    require('jquery.select2-css');
    require('jquery.select2');
    exports.run = function() {

        var editor = EditorFactory.create('#about', 'simple', {extraFileUploadParams:{group:'course'}});

        var $form = $('#user-edit-form');
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
                editor.sync();

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('用户信息保存成功');
                    window.location.reload();
                }).error(function(){
                    Notify.danger('操作失败');
                });
            }
        });

        Validator.addRule('numberUnique', function(options) {
            var v = options.element.val();
            var result=0;
            $numberList=$form.find('.childId');
            for(var i=0;i<$numberList.length;i++){
                if($numberList.eq(i).val()==v){
                    result++;
                }
            }
            return result==1;
        }, '填写的姓名不可重复');

        $form.on('click', '#addNumberBtn', function() {
            var $numberInputNum=$form.find("#numberInputNum");
            var $count=parseInt($numberInputNum.val())+1;
            var $newObject = $(this).parent().parent().prev().clone();

            
            $newObject.find('label').attr('for','id_'+$count);
            $newObject.find('label').hide();
            $newObject.find('.childId').attr('id','id_'+$count);
            $newObject.find('.childId').val('');
            $newObject.find('.select2-container').remove();

            addSelect($newObject.find('.childId'));
            $(this).parent().parent().before($newObject);

            validator.addItem({
                element: $newObject.find('#id_'+$count),
                required: true,
                rule: 'remote numberUnique'
            });
            
            $numberInputNum.val($count);
        });

        $form.delegate('.deleteNumberBtn','click',function() {
            if($form.find('.childId').length>2){
                var $divObject=$(this).parent().parent();
                if($divObject.find('label').is(":visible")){
                    $divObject.next().find('label').show();
                }
                $divObject.find('.childId').each(function(){
                    validator.removeItem($(this));
                });
                $divObject.remove();
            }
        });

        for(var i=0;i<$form.find('.childId').length;i++){

            validator.addItem({
                element: $form.find('#id_'+i),
                required: true,
                rule: 'remote numberUnique'
            });
            addSelect($form.find('#id_'+i));
        }
        
        function addSelect(element){
            element.select2({
                ajax: {
                    url: $("#dataSource").val() + '#',
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
                placeholder: "选择学生",
                createSearchChoice: function() {
                    return null;
                }
            });

            element.parent().parent().find('label').attr('for',element.attr("id"));
        }

        validator.addItem({
            element: '[name="email"]',
            rule: 'email email_remote'
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
            required: true,
            rule: 'remote phone'
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