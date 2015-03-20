define(function(require, exports, module) {
    
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    function checkPrice (){

        var rmb=$('.rmbPrice');
        var num=0;
        $.each(rmb,function(i,item){
           
            rule=/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/i;

            if(!(rule.test(item.value))){
                
                num++;
            }
               
        });

        if(num>0) return false;
        return true;

    }

    function checkRmbPrice(){

        var rmb=$('.cashPrice');
        var num=0;
        $.each(rmb,function(i,item){
           
            rule=/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/i;

            if(!(rule.test(item.value))){
                
                num++;
            }
               
        });

        if(num>0) return false;
        return true;

    }

    exports.run = function() {
        
        var cash_rate=$('#cash-rate').data('val');

        $('#finish').on('click',function(){

            if($('.rmbPrice').length>0){

                if(checkPrice()){

                    $('#coin-model-form').submit();
                }else{
                    $('#help-message').removeClass("hidden");
                }
            }

            if($('.cashPrice').length>0){

                if(checkRmbPrice()){

                    $('#coin-model-form').submit();
                }else{
                    $('#help-message').removeClass("hidden");
                }
            } 

            if ($('.cashPrice').length == 0 || $('.rmbPrice').length == 0) {
                $('#coin-model-form').submit();
            }   

        });

        $('#coin-model-form').on('input','.rmbPrice,.cashPrice',function(){
            
            rule=/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/i;
 
            if(rule.test($(this).val())){ 
                var val=$(this).val();
                var id=$(this).data('id');

                $('#cash'+id).html((cash_rate*val).toFixed(2));
            }

        })

        $('#coin-model-form').on('change','.rmbPrice,.cashPrice',function(){
            
            rule=/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/i;
 
            if(!rule.test($(this).val())){ 

                $(this).val("");
            }

        })

        $('.set').on('click',function(){

            $.each($('.rmb'),function(i,item){
                
                var id=$(this).data('id');
                var val=item.value;

                $('#cash'+id).val((cash_rate*val).toFixed(2));
               
            });

        })

    };

    
});