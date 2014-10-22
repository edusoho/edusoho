define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');

    require('jquery.select2-css');
    require('jquery.select2');

    exports.run = function() {
        var $form = $("#class-editor-form");

        var $modal = $('#class-editor-form').parents('.modal');
        
        $form.on('click','#delete-picture',function(){
            var $container = $(this).parent().find("[id$='container']");
            $container.html('');
            $(this).parent().find('input').val('');
            $(this).hide();
            var u = uploader._uploaders[0];
            var u2 = uploader2._uploaders[0];
            
            u.form.css({
                top: $(u.settings.trigger).offset().top,
                left: $(u.settings.trigger).offset().left,
                width: $(u.settings.trigger).outerWidth(),
                height: $(u.settings.trigger).outerHeight()
            });

            u2.form.css({
                top: $(u2.settings.trigger).offset().top,
                left: $(u2.settings.trigger).offset().left,
                width: $(u2.settings.trigger).outerWidth(),
                height: $(u2.settings.trigger).outerHeight()
            });
        });
        var validator = new Validator({
            element: '#class-editor-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $('#class-editor-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('保存班级成功');
                    window.location.href=$('#backto').attr('href');
                }).error(function(){
                    Notify.danger('保存班级失败');
                });

            }
        });
        validator.addItem({
            element: '#gradeId',
            required: true,
            errormessage:'请选择年级'
        });
        validator.addItem({
            element: '#name',
            required: true,
            errormessage:'请输入班级名称'
        });
        validator.addItem({
            element: '#year',
            required: true,
            errormessage:'请选择入学年份'
        });
        validator.addItem({
            element: '#headteacherid',
            required: true,
            errormessage:'请选择班主任'
        });

        var uploader = new Uploader({
            trigger: '#school-class-icon-upload',
            name: 'icon',
            action: $('#school-class-icon-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传图片失败，请重试！');
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#icon-container").html('<img src="' + response.url + '?'+(new Date()).getTime()+'" style="max-width:400px;">');
                $form.find('[name=icon]').val(response.path);
                $('#icon-container').parent().find('#delete-picture').show();
                Notify.success('上传班级图标成功！');
            }
        }); 
       var uploader2 = new Uploader({
            trigger: '#school-class-backgroundImg-upload',
            name: 'backgroundImg',
            action: $('#school-class-backgroundImg-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传图片失败，请重试！');
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#backgroudImg-container").html('<img src="' + response.url + '?'+(new Date()).getTime()+'" style="max-width:400px;">');
                $form.find('[name=backgroundImg]').val(response.path);
                $('#backgroudImg-container').parent().find('#delete-picture').show();
                Notify.success('上传班级背景图片成功！');
            }
        }); 
    };
     $('#headteacherid').select2({
            ajax: {
                url: app.arguments.teacherUrl + '#',
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
            placeholder: "选择班主任",
            createSearchChoice: function() {
                return null;
            }
        });

});