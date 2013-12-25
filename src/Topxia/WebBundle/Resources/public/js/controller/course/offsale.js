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

    };

});