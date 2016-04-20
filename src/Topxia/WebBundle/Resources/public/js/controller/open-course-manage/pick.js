define(function(require, exports, module) {

    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        require("../../controller/open-course-manage/tab-manage").run();

        var ids=[];
        var $searchForm = $('.form-search');

        $url=$('#sure').data('url');
       
        $('#sure').on('click',function(){
            $('#sure').button('submiting').addClass('disabled');
       
            $.ajax({
                type : "post",
                url : $('#sure').data('url'),
                data : {'ids':ids},
                async : false,
                success : function(response){
                    if (!response['result']) {
                        Notify.danger(response['message']);
                    } else {
                        $('.modal').modal('hide');
                        window.location.reload();
                    }
                    
                }

             });

        });

        $('#search').on('click',function(){

            $.post($searchForm.attr('action'),$searchForm.serialize(),function(data){

                $('.courses-list').html(data);
            });
        });

        $('#enterSearch').keydown(function(event){

            if(event.keyCode == 13){
                $.post($searchForm.attr('action'),$searchForm.serialize(),function(data){

                    $('.courses-list').html(data);
                });
            }
        });
       
      

        $('#all-courses').on('click',function(){

            $.post($(this).data('url'),$('.form-search').serialize(),function(data){

                $('#modal').html(data);
            });

            
        });

        $('.row').on('click',".course-item ",function(){

            var id=$(this).data('id');

            if($(this).hasClass('enabled')){
                return;
            }

            if($(this).hasClass('select')){

                $(this).removeClass('select');
                $('.course-metas-'+id).hide();

                ids = $.grep(ids, function(val, key) {

                    if(val != id )
                        return true;
                }, false);

            }else{
                $(this).addClass('select');
                
                $('.course-metas-'+id).show();

                ids.push(id);

            }

        });


    };

    
});