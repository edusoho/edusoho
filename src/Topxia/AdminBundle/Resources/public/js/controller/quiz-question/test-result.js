define(function(require, exports, module) {

    var wrongs = [],

    rights = [],

    alls = [];

    exports.run = function() {

        $('.answerCard .panel-body a.btn[href^="#question"]').each(function(){

            if ($(this).hasClass('wrong')) {
                wrongs.push($(this).attr('href'));
            }
            if ($(this).hasClass('right')) {
                rights.push($(this).attr('href'));
            }
            alls.push($(this).attr('href'));
        });


        $('.answerCard').on('click', '#showWrong', function(){
            $.each(alls, function(index, val){
                if ($.inArray(val, wrongs) < 0) {
                    $(val).toggle();
                }
            });

            $('.testpaper-question-block').each(function(){
                var isHidden = true;
                $(this).find('div.testpaper-question').each(function(){
                    id = $(this).attr('id');   

                    if ($.inArray('#'+id, wrongs) >= 0) {
                        isHidden = false;
                    }
                });

                if (isHidden){
                    $(this).toggle();
                }
            });

        });

        // $('.answerCard').on('click', '#showWrong', function(){
            
            // $.each(alls, function(index, val){
            //     if ($.inArray(val, wrongs) < 0) {
            //         $(val).hide();
            //     } else {
            //         $(val).show();
            //     }
            // });
            // $(this).parent().find('.btn').removeClass('btn-info');
            // $(this).addClass('btn-info');
        // });

        // $('.answerCard').on('click', '#showNotRight', function(){
            
        //     $.each(alls, function(index, val){
        //         if ($.inArray(val, rights) < 0) {
        //             $(val).hide();
        //         } else {
        //             $(val).show();
        //         }
        //     });
        //     $(this).parent().find('.btn').removeClass('btn-info');
        //     $(this).addClass('btn-info');
        // });

        // $('.answerCard').on('click', '#showAll', function(){
            
        //     $.each(alls, function(index, val){
        //         $(val).show();
        //     });
        //     $(this).parent().find('.btn').removeClass('btn-info');
        //     $(this).addClass('btn-info');
        // });

        $.each(alls, function(index, val){
            $(val).on('click', '.testpaper-question-actions a.analysis-btn', function(){
                $(this).parents('.testpaper-question').find('div.well').show();
                $(this).parent().find('.unanalysis-btn').show();
                $(this).hide()
            });

            $(val).on('click', '.testpaper-question-actions a.unanalysis-btn', function(){
                $(this).parents('.testpaper-question').find('div.well').hide();
                $(this).parent().find('.analysis-btn').show();
                $(this).hide();
            });
        });

        $('.row input').attr('disabled', true);
        $('textarea').attr('disabled', true);

        $('body').on('click', '.favorite-btn', function(){
            $btn = $(this);
            $.post($(this).data('url'),function(){
                $btn.hide();
                $btn.parent().find('.unfavorite-btn').show();
            });
        });

        $('body').on('click', '.unfavorite-btn', function(){
            $btn = $(this);
            $.post($(this).data('url'),function(){
                $btn.hide();
                $btn.parent().find('.favorite-btn').show();
            });
        });

    };


});

