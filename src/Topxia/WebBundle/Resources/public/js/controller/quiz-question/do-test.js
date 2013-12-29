define(function(require, exports, module) {

    exports.run = function() {

        // setInterval("", 50);

        var changeAnswers = {};

        $('input[name]').each(function(){

            $(this).change(function(){
                var name = $(this).attr('name');

                var values = [];
                $('input[name='+name+']:checked').each(function(){
                    values.push($(this).val());
                })
                changeAnswers[name] = values.join(',');

                console.log(changeAnswers);
            })

        });

        $('body').on('click', '#postPaper', function(){

            $.post($(this).data('url'), {data:changeAnswers}, function(){
                changeAnswers = {};
            });

        })
    };

});

