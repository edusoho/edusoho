define(function(require, exports, module) {
        
    exports.run = function() {
        $(".choose-all").click(function() {
           if( $(this).is(":checked") == true){
                $("input[name='note-item']").prop("checked", true);
            } else {
                $("input[name='note-item']").prop("checked", false);
            }
        });

        $(".note-delete").on('click', function(){
            var ids = [];
            $("input[name='note-item']:checked ").each(function() {
                ids.push($(this).attr("value"));
            });
            
            if(ids.length == 0){
                return ;
            }

            if (!confirm('真的要删除这些话题吗？')) {
                return ;
            }

            $.post($(this).data('url'), {ids:ids}, function(){
                 window.location.reload();
            }); 
        });

        $(".note-tr").on('click', function(){
            $(this).find('input[name=note-item]').click();
        });
    };
});