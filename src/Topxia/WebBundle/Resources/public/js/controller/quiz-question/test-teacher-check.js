define(function(require, exports, module) {

    exports.run = function() {


        $('.answerCard').on('click', '#postPaper', function(){
            $finishBtn = $(this);

            $.post($(this).data('url'), $('#essayForm').serialize(), function(){
                window.location.href = $finishBtn.data('goto');
            });

        });

        $('.answerCard .panel-body a.btn[href^="#question"]').each(function(){
            var val = $(this).attr('href');
            $(val).on('click', '.panel-footer a.btn', function(){
                $(this).parent().find('div.well').toggle();
            })
        });
    };


});

