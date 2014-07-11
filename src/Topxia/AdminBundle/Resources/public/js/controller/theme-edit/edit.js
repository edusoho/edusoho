define(function(require, exports, module) {

    require('jquery.sortable');

    var ThemeManage = require('./theme-manage');

    exports.run = function() {

        var $list = $(".module-item-list").sortable({
            distance: 20,
            onDrop: function (item, container, _super) {
                _super(item, container);
                sortList($list);
                themeManage.getElement().trigger('save_config');
            },
            serialize: function(parent, children, isContainer) {
                return isContainer ? children : parent.attr('id');
            }
        });

        $("#iframepage").load(function(){
            var mainheight = $(this).contents().find("body").height()+420;
            $(this).height(mainheight);
        }); 


        var themeManage = new ThemeManage({
            element: '#theme-edit-content',
            config: $.parseJSON($('#theme-config').text()),
            allConfig: $.parseJSON($('#theme-all-config').text())
        });

        $('body').data('themeManage', themeManage);


        $("#theme-edit-content .theme-edit-block").on('click', '.item-edit-btn', function(){
            themeManage.setCurrentItem($(this).parents('li.theme-edit-item'));
        });

        $("#theme-edit-content").on("click", '.check-block', function(){
            event.stopPropagation();
            if ($(this).prop('checked') == true) {
                $(this).parents('li').find('.item-edit-btn').show();
            } else {
                $(this).parents('li').find('.item-edit-btn').hide();
            }
            themeManage.getElement().trigger('save_config');
        });

        $("#theme-edit-content").on("click", '.check-box', function(){
            event.stopPropagation();
            themeManage.getElement().trigger('save_config');
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