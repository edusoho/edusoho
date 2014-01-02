define(function(require, exports, module) {

    exports.run = function() {

        // setInterval("", 50);

        var changeAnswers = {};

        $('*[data-type]').each(function(index){

            $(this).on('change', function(){
                var name = $(this).attr('name');

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
                    values.push($(this).val());     

                }
                changeAnswers[name] = values.join(',');

                console.log(changeAnswers);
            })

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

