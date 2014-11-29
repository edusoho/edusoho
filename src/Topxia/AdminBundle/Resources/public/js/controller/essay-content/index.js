define(function(require, exports, module) {

    require('jquery.sortable');
    var Sticky = require('sticky');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('.course-publish-btn').click(function() {
            if (!confirm('您真的要发布该文章吗？')) {
                return ;
            }

            $.post($(this).data('url'), function() {
                window.location.reload();
            });
        });

        var sortList = function($list) {
            var data = $list.sortable("serialize").get();
            $.post($list.data('sortUrl'), {ids:data}, function(response){
                var contentNum = chapterNum = unitNum = 0;

                $list.find('.item-essay-content, .item-chapter').each(function() {
                    var $item = $(this);
                    if ($item.hasClass('item-essay-content')) {
                        contentNum ++;
                        $item.find('.number').text(contentNum);
                    } else if ($item.hasClass('item-chapter-unit')) {
                        unitNum ++;
                        $item.find('.number').text(unitNum);
                    } else if ($item.hasClass('item-chapter')) {
                        chapterNum ++;
                        unitNum = 0;
                        $item.find('.number').text(chapterNum);
                    }

                });
            });
        };

        var $list = $("#essay-item-list").sortable({
            distance: 20,
            onDrop: function (item, container, _super) {
                _super(item, container);
                sortList($list);

            },
            serialize: function(parent, children, isContainer) {
                return isContainer ? children : parent.attr('id');
            }
        });

        $list.on('click', '.delete-chapter-btn', function(e) {
            if (!confirm('您真的要删除该章节吗？')) {
                return ;
            }
            var $btn = $(e.currentTarget);
            $.post($(this).data('url'), function(response) {
                $btn.parents('.item-chapter').remove();
                sortList($list);
                Notify.success('章节已删除！');
            }, 'json');
        });

        Sticky('.essay-content-panel .panel-heading', 0, function(status){
            if (status) {
                var $elem = this.elem;
                $elem.addClass('sticky');
                $elem.width($elem.parent().width() - 10);
            } else {
                this.elem.removeClass('sticky');
                this.elem.width('auto');
            }
        });

        $('.dropdown-menu').parent().on('shown.bs.dropdown', function () {
            if ($(this).find('.dropdown-menu-more').css('display') == 'block') {
                $(this).parent().find('.dropdown-menu-more').mouseout(function(){
                    $(this).parent().find('.dropdown-menu-more').hide();
                });

                 $(this).parent().find('.dropdown-menu-more').mouseover(function(){
                    $(this).parent().find('.dropdown-menu-more').show();
                });

            } else {
                $(this).parent().find('.dropdown-menu-more').show();
            }
        });

        $('.dropdown-menu').parent().on('hide.bs.dropdown',function() {
            $(this).find('.dropdown-menu-more').show();
        });
    };

});