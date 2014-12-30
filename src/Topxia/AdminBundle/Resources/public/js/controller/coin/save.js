define(function(require, exports, module) {
    
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    Validator.addRule('checkPrice',function(options){

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

    },'{{display}}只能为正数,且保留两位小数');

    Validator.addRule('checkRmbPrice',function(options){

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

    },'{{display}}只能为正数,且保留两位小数');

    exports.run = function() {
        
        var cash_rate=$('#cash-rate').data('val');

        var validator = new Validator({
            element: '#coin-model-form',
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $('#finish').button('submiting').addClass('disabled');
            },
        })

        $('.rmbPrice').on('input',function(){
            
            rule=/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/i;
 
            if(rule.test($(this).val())){ 
                var val=$(this).val();
                var id=$(this).data('id');

                $('#cash'+id).html((cash_rate*val).toFixed(2));
            }

        })

        $('.set').on('click',function(){

            $.each($('.rmb'),function(i,item){
                
                var id=$(this).data('id');
                var val=item.value;

                $('#cash'+id).val((cash_rate*val).toFixed(2));
               
            });

        })

        if($('.rmbPrice').length>0){
            validator.addItem({
                element: '.rmbPrice',
                required: true,
                rule: 'checkPrice' 
            });
        }

        if($('.cashPrice').length>0){
        validator.addItem({
            element: '.cashPrice',
            required: true,
            rule: 'checkRmbPrice' 
        });
        }

    };

    
});