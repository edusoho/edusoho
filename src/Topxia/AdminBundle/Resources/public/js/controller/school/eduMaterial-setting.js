define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');

    exports.run = function() {
        var $table=$('.eduMaterial-table');
        $table.popover({
            selector: '.material-selector',
            trigger: 'click',
            placement: 'bottom',
            html: true,
            delay: 30,
            viewport: { selector: '.eduMaterial-table', padding: 0 },
            content: function() {
                return $(this).find('.material-list').html();
            }
        });

        $table.on('click','.material-name',function(){
            if($(this).closest('.materialTd').find('.eduMaterial-name').html()==$(this).html()){
                $table.find('.popover').parent().find('.material-selector').popover('hide');
                return;
            }
            if (!confirm('确认更改教材为'+$(this).html()+'？')) {
                return ;
            }
            $(this).closest('.materialTd').find('.eduMaterial-name').html($(this).html());
            $table.find('.popover').parent().find('.material-selector').popover('hide');
            $.post(
                $(this).data('url'),
                { eduMaterialId:$(this).data('edumaterialid'),
                  materialId:$(this).data('materialid')
                },
                function(data){
                    if(data){
                        Notify.success('修改教材成功');
                    }else{
                        Notify.danger('修改教材失败');
                    }
                }
            );
        });

        $('body').on('click', function () {
            var pops=$table.find('.popover');
            if(pops.length>0){
                pops.parent().find('.material-selector').popover('hide');
            }
        });


    }
});