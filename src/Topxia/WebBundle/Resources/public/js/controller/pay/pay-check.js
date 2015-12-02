define(function(require, exports, module){

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var $form = $('#verify-form');
        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $.post($form.attr('action'), $form.serialize(),function(data){
                    if(data.ret_code == '0000'){

                        $("#pay-check-form").show();
                        $('#verify-form').hide();


                        $("#pay-check-btn").text("确认支付").data('target',"#pay-check-form");

                        $('#js-time-left').html('120');
                        $('#js-fetch-btn-text').html('秒后重新获取');
                        //Notify.success('发送短信成功');
                        $('.js-sms-send').addClass('disabled');
                        refreshTimeLeft();
                    }
                });
            }
        });

        if ($("#getcode_num").length > 0){

            $("#getcode_num").click(function(){ 
                $(this).attr("src",$("#getcode_num").data("url")+ "?" + Math.random()); 
            }); 

            validator.addItem({
                element: '[name="captcha_code"]',
                required: true,
                rule: 'alphanumeric remote',
                display: '验证码',
                onItemValidated: function(error, message, eleme) {
                    if (message == "验证码错误"){
                        $("#getcode_num").attr("src",$("#getcode_num").data("url")+ "?" + Math.random()); 
                    }
                }               
            });
        };

        var $form2 = $('#pay-check-form');
        var validator2 = new Validator({
            element: $form2,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                alert($form2.attr('action'));
                $.post($form2.attr('action'), $form2.serialize(),function(data){
                    console.log(data);
                });
            }
        });

        validator2.addItem({
            element: '[name="verify_code"]',
            required: true,
            rule: 'integer fixedLength{len:6}',
            display: '短信验证码',         
        });

        $(".js-sms-send").on("click",function(){

            $("#verify-form").show();
            $('#pay-check-form').hide();
            $("#pay-check-btn").removeClass('disabled');
            // console.log($form.serialize());
            // $.post($(this).data('url'), $form.serialize(),function(data){
            //     if(data.ret_code == '0000'){
            //         $(this).parents('.form-group').show();
            //         $(this).parents('.form-group').prev().hide();
            //         $('#js-time-left').html('120');
            //         $('#js-fetch-btn-text').html('秒后重新获取');
            //         //Notify.success('发送短信成功');
            //         $('.js-sms-send').addClass('disabled');
            //         refreshTimeLeft();
            //     }
            // });   
        })

        var refreshTimeLeft = function(){
            var leftTime = $('#js-time-left').html();
            $('#js-time-left').html(leftTime-1);
            if (leftTime-1 > 0) {
                setTimeout(refreshTimeLeft, 1000);
            } else {
                $('#js-time-left').html('');
                $('#js-fetch-btn-text').html('获取短信验证码');
                $('.js-sms-send').removeClass('disabled');
            }
        };
        
    };

});