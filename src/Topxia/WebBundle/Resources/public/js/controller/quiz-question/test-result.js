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
                    $(val).hide();
                } else {
                    $(val).show();
                }
            });
            $(this).parent().find('.btn').removeClass('btn-info');
            $(this).addClass('btn-info');
        });

        $('.answerCard').on('click', '#showNotRight', function(){
            
            $.each(alls, function(index, val){
                if ($.inArray(val, rights) < 0) {
                    $(val).hide();
                } else {
                    $(val).show();
                }
            });
            $(this).parent().find('.btn').removeClass('btn-info');
            $(this).addClass('btn-info');
        });

        $('.answerCard').on('click', '#showAll', function(){
            
            $.each(alls, function(index, val){
                $(val).show();
            });
            $(this).parent().find('.btn').removeClass('btn-info');
            $(this).addClass('btn-info');
        });

        $.each(alls, function(index, val){
            $(val).on('click', '.panel-footer a.btn', function(){
                $(this).parent().find('div.well').toggle();
            })
        });
    };


});

