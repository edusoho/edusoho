define(function(require, exports, module) {

    exports.run = function() {

        // setInterval("", 50);

        var changeAnswers = Array();

        $('input[name]').each(function(){

            $(this).change(function(){
                var name = $(this).attr('name');
                var value = $(this).val();

                if (name in changeAnswers){
                    if ($.inArray(value, changeAnswers[name]) >= 0) {
                        changeAnswers[name].splice($.inArray(value, changeAnswers[name]),1);
                    } else {
                        changeAnswers[name].push(value);
                    }
                } else {
                    changeAnswers[name] = Array(value);
                }

            })

        });

        $('body').on('click', '#postPaper', function(){           

            console.log(changeAnswers);

            changeAnswers = Array();

        })
    };

});