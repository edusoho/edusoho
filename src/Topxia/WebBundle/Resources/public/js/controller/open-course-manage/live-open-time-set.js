define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    
    exports.run = function() {
       
        var $content = $("#live-lesson-content-field");
        var $form = $('#live-open-course-form');

        var now = new Date();
        var validator = new Validator({
            
            element: $form,
            autoSubmit: true,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#live-open-course-form-btn').button('submiting').addClass('disabled');
            }
        });

        Validator.addRule('remote_check',
            function(options, commit) {

                var element = $('#live_lesson_time_check');
                var startTime = $('[name=startTime]').val();
                var length = $('[name=timeLength]').val();

                if(startTime && length) {
                    url = element.data('url');
                    $.get(url, {startTime:startTime,length:length}, function(response) {
                        commit(response.success, response.message);
                    }, 'json');
                }else{
                    return true;
                }
            });

        Validator.addRule('live_date_check',
            function() {

                var thisTime = $('[name=startTime]').val();
                thisTime = thisTime.replace(/-/g,"/");
                thisTime = Date.parse(thisTime)/1000;
                var nowTime = Date.parse(new Date())/1000;

                if (nowTime <= thisTime) {
                    return true;
                }else{
                    return false;
                }
            },"请输入一个晚于现在的时间"

        );

        var thisTime = $('[name=startTime]').val();
            thisTime = thisTime.replace(/-/g,"/");
            thisTime = Date.parse(thisTime)/1000;
            var nowTime = Date.parse(new Date())/1000;

        if (nowTime > thisTime) {
            $('[name=startTime]').attr('disabled',true);
            $('#live-length-field').attr('disabled',true);
            $('#live-open-course-form-btn').attr('disabled',true);
            
            $('#starttime-help-block').html("直播已经开始或者结束,无法编辑");
            $('#starttime-help-block').css('color','#a94442');
            $('#timelength-help-block').html("直播已经开始或者结束,无法编辑");
            $('#timelength-help-block').css('color','#a94442');
        }else{
            $('[name=startTime]').attr('disabled',false);
            $('#live-open-course-form-btn').attr('disabled',false);
        }


        validator.addItem({
            element: '[name=startTime]',
            required: true,
            rule:'live_date_check',
            errormessageRequired: '请输入直播的开始时间'
        });   

        validator.addItem({
            element: '[name=timeLength]',
            required: true,
            rule:'positive_integer remote_check',
            display: '直播时长',
            onItemValidated: function(error, message, elem) {
                if (error) {
                    return ;
                }

                var params = {startTime: $('[name=startTime]').val(), length: $('[name=timeLength]').val()};

                if (!params.startTime) {
                    return ;
                }

                $.get($(elem).data('calculateLeftCapacityUrl'), params, function(response) {
                    var maxStudentNum = parseInt($(elem).data('maxStudentNum'));
                    var leftCapacity = parseInt(response);
                    if ( maxStudentNum > leftCapacity) {
                       var message = '在此时间段内开课，将会超出教室容量<strong>' + (maxStudentNum - leftCapacity) + '</strong>人，届时有可能会导致满额后部分学员无法进入直播。';
                        $(elem).parent().find('.help-block').html('<div class="alert alert-warning">' + message + '</div>');
                    }
                }, 'json');

            }
        });
     
        $("[name=startTime]").datetimepicker({
            language: 'zh-CN',
            autoclose:true
        }).on('hide', function(ev){
            validator.query('[name=startTime]').execute();
        });
        $('[name=startTime]').datetimepicker('setStartDate', now);


        validator.on('formValidate', function(elemetn, event) {
            editor.updateElement();
            var z = editor.getData();
            var x = editor.getData().match(/<embed[\s\S]*?\/>/g);
            if (x) {
                for (var i = x.length - 1; i >= 0; i--) {
                   var y = x[i].replace(/\/>/g,"wmode='Opaque' \/>");
                   var z =  z.replace(x[i],y);
                };
            }
            $content.val(z);
        });
    };

});