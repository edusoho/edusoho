define(function(require, exports, module) {

    var Widget = require('widget');

    exports.run = function() {

        var ids=[];

        $('#sure').on('click',function(){
            $('#sure').button('submiting').addClass('disabled');
       
            $.ajax({
                type : "post",
                url : $('#sure').data('url'),
                data : "ids="+ids,
                async : false,
                success : function(data){
                    
                    $('.modal').modal('hide');
                    window.location.reload();
                }

             });

        });

        $('#search').on('click',function(){

            if($('[name=key]').val() != "" ){

                $.post($(this).data('url'),$('.form-search').serialize(),function(data){

                    $('.courses-list').html(data);
                });
            }

        });

        $('.courses-list').on('click', '.pagination li', function() {
            var url = $(this).data('url');
            if (typeof(url) !== 'undefined') {
                $.post(url, $('.form-search').serialize(),function(data){
                    $('.courses-list').html(data);
                });
            }
        });

        $('#enterSearch').keydown(function(event){

            if(event.keyCode==13){

                $.post($(this).data('url'),$('.form-search').serialize(),function(data){

                    $('.courses-list').html(data);
                
                });
                return false;
            }
        });

        $('#all-courses').on('click',function(){

            $.post($(this).data('url'),$('.form-search').serialize(),function(data){

                $('#modal').html(data);
            });

            
        });

        $('.js-course-select').on('change', function(){
            var id = $(this).val();
            var sid = $(this).attr('id').split("-")[2];
            for (var i = 0; i < ids.length; i++) {
                var idArr = ids[i].split(":");
                if(idArr[0] == sid){
                    ids[i] = sid + ":" + id;
                    break;
                }
            }
        });

        $('.row').on('click',".course-item ",function(){

            var sid=$(this).data('id');//courseSet.id

            if($(this).hasClass('enabled')){
                return;
            }
            var id = $('#course-select-'+sid).val();
            if($(this).hasClass('select')){

                $(this).removeClass('select');
                $('.course-metas-'+sid).hide();

                ids = $.grep(ids, function(val, key) {

                    if(val != sid + ":" + id )
                        return true;
                }, false);

            }else{
                $(this).addClass('select');
                
                $('.course-metas-'+sid).show();

                ids.push(sid + ':' + id);

            }

        });


    };

    
});