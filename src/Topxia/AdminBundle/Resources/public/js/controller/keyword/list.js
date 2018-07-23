define(function(require, exports, module) {

    exports.run = function() {
        $('#keyword-table').on('click','.delete-btn',function(){

            if (!confirm(Translator.trans('admin.keyword.delete_hint'))) {
                return ;
            }

            $.post($(this).data('url'),function(){

                window.location.reload();

            });
        });

        $('#keyword-table').on('click','.replaced-btn',function(){
            $.post($(this).data('url'),function(){
                window.location.reload();
            });
        });

        $('#keyword-table').on('click','.banned-btn',function(){
            $.post($(this).data('url'),function(){
                window.location.reload();
            });
        });

        $('body').on('click', '#replaced' ,function(){
            var $this = $(this);
            var text = $this.data('text');
            $this.siblings('.help-block').text(text);
        });

        $('body').on('click', '#banned' ,function(){
            var $this = $(this);
            var text = $this.data('text');
            $this.siblings('.help-block').text(text);
        });
    };

});