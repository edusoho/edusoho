define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    require('jquery.select2-css');
    require('jquery.select2');
    exports.run = function() {

        var $form = $('#user-create-form');
        var $modal= $('#user-create-form').parent('.modal');
        var validator = new Validator({
            element: '#user-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $('#user-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('新用户添加成功');
                    window.location.reload();
                }).error(function(){
                    Notify.danger('新用户添加失败');
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
            //select插件会额外插一个该class的div，所以要大于２
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

        addSelect($("#id_1"));

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
            element: '#id_1',
            required: true,
            rule: 'remote numberUnique'
        });

        validator.addItem({
            element: '[name="mobile"]',
            required: true,
            rule: 'remote phone'
        });

        validator.addItem({
            element: '[name="relation"]',
            required: true
        });

        validator.addItem({
            element: '[name="truename"]',
            required: true,
            rule: 'chinese minlength{min:2} maxlength{max:5}'
        });

        validator.addItem({
            element: '[name="email"]',
            rule: 'email email_remote'
        });

        validator.addItem({
            element: '[name="password"]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}'
        });

        validator.addItem({
            element: '[name="confirmPassword"]',
            required: true,
            rule: 'confirmation{target:#password}'
        });
    };

});