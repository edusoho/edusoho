define(function(require, exports, module) {

    require('jquery.sortable');

    var ThemeManage = require('./theme-manage');

    exports.run = function() {

        // $("#middle-banner-edit").on('click',function(){
        //     window.open($(this).data('url'));
        //     return;
        // });

        var $list = $(".module-item-list").sortable({
            distance: 20,
            itemSelector: '.theme-edit-item',
            onDrop: function (item, container, _super) {
                _super(item, container);
                sortList($list);
                themeManage.getElement().trigger('save_sort');
            },
            serialize: function(parent, children, isContainer) {
                return isContainer ? children : parent.attr('id');
            }
        });

        $("#iframepage").load(function(){
            var mainheight = $(this).contents().find("body").height();
            $(this).height(mainheight);
        }); 


        var themeManage = new ThemeManage({
            element: '#theme-edit-content',
            config: $.parseJSON($('#theme-config').html()),
            allConfig: $.parseJSON($('#theme-all-config').html()),
            currentIframe: $('#iframepage')
        });

        $('body').data('themeManage', themeManage);


        $("#theme-edit-content .theme-edit-block").on('click', '.item-edit-btn', function(){
            themeManage.setCurrentItem($(this).parents('li.theme-edit-item'));
        });

        $("#theme-edit-content").on("click", '.check-block', function(event){
            event.stopPropagation();
            themeManage.setCurrentItem($(this).parents('li.theme-edit-item'));
           
            if ($(this).prop('checked') == true) {
                $(this).parents('li').find('.item-edit-btn,.item-set-btn').show();
            } else {
                $(this).parents('li').find('.item-edit-btn,.item-set-btn').hide();
            }

            themeManage.getElement().trigger('save_config');
        });

        $("#theme-edit-content").on("click", '.check-box', function(event){
            event.stopPropagation();
            themeManage.getElement().trigger('save_config');
        });

        $("#theme-edit-content").on("change", 'input[type="text"]', function(event){
            themeManage.getElement().trigger('save_config');
            return false;
        });

    };

    var sortList = function($list) {
            var data = $list.sortable("serialize").get();
            $.post($list.data('sortUrl'), {ids:data}, function(response){
                var lessonNum = chapterNum = unitNum = 0;

                // $list.find('.item-lesson, .item-chapter').each(function() {
                //     var $item = $(this);
                //     if ($item.hasClass('item-lesson')) {
                //         lessonNum ++;
                //         $item.find('.number').text(lessonNum);
                //     } else if ($item.hasClass('item-chapter-unit')) {
                //         unitNum ++;
                //         $item.find('.number').text(unitNum);
                //     } else if ($item.hasClass('item-chapter')) {
                //         chapterNum ++;
                //         unitNum = 0;
                //         $item.find('.number').text(chapterNum);
                //     }

                // });
            });
        };

});