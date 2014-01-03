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
                        if ($(this).val() != "") {
                            values.push($(this).val());
                        }
                    });

                }
                //essay
                if ($(this).data('type') == 'essay') {
                    if ($(this).val() != "") {
                        values.push($(this).val());
                    }     

                }
                changeAnswers[name] = values.join(',');


                if (values.length > 0) {
                    $('a[href="#question' + name + '"]').css('background-color', '#f5f5f5');
                } else {
                    $('a[href="#question' + name + '"]').css('background-color', 'inherit');
                }


            });

        });

        $('.panel-heading').on('click', 'a.btn', function(){
            id = $(this).parents('.panel').attr('id');
            btn = $('.answerCard .panel-body [href="#'+id+'"]');
            if (btn.css('border-left-width') == '1px') {
                btn.css('border', '2px solid #428bca');
            } else {
                btn.css('border', '1px solid #CCCCCC');
            }
        });

        $('body').on('click', '#postPaper', function(){

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

