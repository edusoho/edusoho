define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        $("#haveoffsale").on('click',function(){
           
            if( $(this).is(":checked") == true){
                $("#course_promoCode").attr('disabled',false);
            }else{
                 $("#course_promoCode").val('');
                 $("#course_promoCode").attr('disabled',true);
            }
        
        });
       
        var validator = new Validator({
            element: '#course-buy-form'           
        });

        if ($('#course-buy-form').find('input[name="promoCode"]').length > 0){
            validator.addItem({
                element: '[name="promoCode"]',
                required: false,
                rule: 'remotePost',
                hideMessage:function(msg,ele,eve){               
                    if(null != msg ){
                        $("#promoCode_info").html(msg);
                        $("#promoCode_info").addClass('text-color-green');
                    }
                }          
             });
        }


        if ($('#course-buy-form').find('input[name="mobile"]').length > 0){
            validator.addItem({
                element: '[name="mobile"]',
                rule: 'phone',
                required: true
            });
        }

        if ($('#course-buy-form').find('input[name="truename"]').length > 0){
            validator.addItem({
                element: '[name="truename"]',
                rule: 'chinese byte_minlength{min:4} byte_maxlength{max:10}',
                required: true
            });
        }

        if ($('#course-buy-form').find('input[name="qq"]').length > 0){
            validator.addItem({
                element: '[name="qq"]',
                rule: 'qq'
            });
        }


    };

});