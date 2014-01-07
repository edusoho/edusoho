define(function(require, exports, module) {

    exports.run = function() {

        // setInterval("", 50);

        var changeAnswers = {};

        $('*[data-type]').each(function(index){
            var name = $(this).attr('name');

            $(this).on('change', function(){
                // var name = $(this).attr('name');

                var values = [];
                //choice
                if ($(this).data('type') == 'choice') {

                    $('input[name='+name+']:checked').each(function(){
                        values.push($(this).val());
                    });

                }
                //determine
                if ($(this).data('type') == 'determine') {

                    $('input[name='+name+']:checked').each(function(){
                        values.push($(this).val());
                    });

                }
                //fill
                if ($(this).data('type') == 'fill') {

                    $('input[name='+name+']').each(function(){
                        values.push($(this).val());
                    });

                }
                //essay
                if ($(this).data('type') == 'essay') {
                    if ($(this).val() != "") {
                        values.push($(this).val());
                    }     

                }

                changeAnswers[name] = values;


                if (values.length > 0) {
                    $('a[href="#question' + name + '"]').addClass('done');
                } else {
                    $('a[href="#question' + name + '"]').removeClass('done');
                }


            });

        });

        $('.panel-footer').on('click', 'a.btn', function(){
            id = $(this).parents('.panel').attr('id');
            btn = $('.answerCard .panel-body [href="#'+id+'"]');
            if (btn.css('border-left-width') == '1px') {
                btn.addClass('have-pro');
            } else {
                btn.removeClass('have-pro');
            }
        });

        $('body').on('click', '#postPaper, #finishPaper', function(){

            $.post($(this).data('url'), {data:changeAnswers}, function(){
                changeAnswers = {};
            });

        });




        $('.choice').on('click', 'ul li', function(){
            $input = $(this).parents('div.choice').find('.panel-footer label').eq($(this).index()).find('input');
            isChecked = $input.prop("checked");
            $input.prop("checked", !isChecked).change();
            
        });
    };

});

