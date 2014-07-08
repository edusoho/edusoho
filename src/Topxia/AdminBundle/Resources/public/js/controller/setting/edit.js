define(function(require, exports, module) {

    require('jquery.sortable');


    var themeModal = require('./theme-modal');

    exports.run = function() {

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


        var $list = $(".module-item-list").sortable({
            distance: 20,
            onDrop: function (item, container, _super) {
                _super(item, container);
                sortList($list);

            },
            serialize: function(parent, children, isContainer) {
                return isContainer ? children : parent.attr('id');
            }
        });

        $("#iframepage").load(function(){
            var mainheight = $(this).contents().find("body").height()+420;
            $(this).height(mainheight);
        }); 



        var currentConfig = $.parseJSON($("#fuck").text());

        themeModal.setAll(currentConfig);
    };

});