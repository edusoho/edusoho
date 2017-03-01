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
                    errormessageRequired: Translator.trans('请输入虚拟币价格')
                });
            });
        }

        if ($('.rate').length > 0) {
            $('.rate').each(function(){
                validator.addItem({
                    element: $(this),
                    required: true,
                    rule: 'percent_number',
                    errormessageRequired: Translator.trans('请输入最大可抵扣比率')
                });
            });
        }

        $('#coin-model-form').on('input','.cashPrice',function(){
            
            rule=/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/i;
 
            if(rule.test($(this).val())){ 
                var val=$(this).val();
                var id=$(this).data('id');
                var rmb_price = $('#rmb'+id).html();

                $('#deRmb'+id).html((rmb_price*val/100).toFixed(2));
                $('#cash'+id).html((rmb_price*val/100*cash_rate).toFixed(2))

            }

        })

        $('#coin-model-form').on('change','.cashPrice',function(){
            
            rule=/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/i;
 
            if(!rule.test($(this).val())){ 

                $(this).val("");
            }

        })

        $('#coin-model-form').on('change','.rate',function(){
            
            rule= /^(100|[1-9]\d|\d)$/;
            var value = Number($(this).val());
            value = parseInt(value);
            if (value > 100) {
                $(this).val(100);
            }
            else if(value > 0 && value < 100){
                $(this).val(value);
            }
            if(!rule.test($(this).val())){ 

                $(this).val(0);
            }

            var val=$(this).val();
            var id=$(this).data('id');
            var rmb_price = $('#rmb'+id).data('val');

            $('#deRmb'+id).html((rmb_price*val/100).toFixed(2));
            $('#cash'+id).html((rmb_price*val/100*cash_rate).toFixed(2))

        })

        $('.set').on('click',function(){

            $.each($('.rmb'),function(i,item){
                
                var id=$(this).data('id');
                var val=item.value;

                $('#cash'+id).val((cash_rate*val).toFixed(2));
               
            });

        })

        $("#typelist").on('click','.js-type-filter',function(){
            var $btn = $(this);
            $.get($btn.data('url'),function(html){
            $('#typelist').html(html);
            $("input[name='type']").val($btn.attr("id"));
            })
        })

    };

    
});