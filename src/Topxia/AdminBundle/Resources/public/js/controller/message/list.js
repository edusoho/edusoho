define(function(require, exports, module) {
        
    exports.run = function() {

        $(".choose-all").click(function() {
           if( $(this).is(":checked") == true){
                $("input[name='message-item']").prop("checked", true);
            } else {
                $("input[name='message-item']").prop("checked", false);
            }
        });

        $(".message-delete").on('click', function(){
            var ids = [];
            $("input[name='message-item']:checked ").each(function() {
                ids.push($(this).attr("value"));
            });
            
            if(ids.length == 0){
                return ;
            }

            if (!confirm('真的要删除这些私信内容吗？')) {
                return ;
            }

            $.post($(this).data('url'), {ids:ids}, function(){
                window.location.reload();
            }); 
        });
        
        $(".message-tr").on('click', function(){
            $(this).find('input[name=message-item]').click();
        });

    };

  });

