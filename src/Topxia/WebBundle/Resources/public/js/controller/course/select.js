define(function(require, exports, module) {

    var Widget = require('widget');

    exports.run = function() {

        $('#sure').on('click',function(){

            $('#sure').button('submiting').addClass('disabled');

        });

        $('.course-wide-list').on('click',".course-item ",function(){

            var id=$(this).data('id');

            if($(this).hasClass('select')){

                $(this).removeClass('select');
                $('.course-metas-'+id).hide();

            }else{
                $(this).addClass('select');
                
                $('.course-metas-'+id).show();
            }

        });

    };

    
});