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
        
        var cash_rate = $('#cash-rate').data('val');

        var validator = new Validator({
            element: '#coin-model-form'
        });

        if ($('.cashPrice').length > 0) {
            $('.cashPrice').each(function(){
                validator.addItem({
                    element: $(this),
                    required: true,
                    rule: 'float',
                    errormessageRequired: '请输入虚拟币价格'
                });
            });
        }


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