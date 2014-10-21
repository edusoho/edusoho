define(function(require, exports, module) {

    require('jquery.sortable');
    var Sticky = require('sticky');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        require('./header').run();

        var sortList = function($list) {
            var data = $list.sortable("serialize").get();
            $.post($list.data('sortUrl'), {ids:data}, function(response){
                var lessonNum = chapterNum = unitNum = 0;

                $list.find('.item-lesson, .item-chapter').each(function() {
                    var $item = $(this);
                    if ($item.hasClass('item-lesson')) {
                        lessonNum ++;
                        $item.find('.number').text(lessonNum);
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

        var $list = $("#course-item-list").sortable({
            distance: 20,
            onDrop: function (item, container, _super) {
                _super(item, container);
                sortList($list);

            },
            serialize: function(parent, children, isContainer) {
                return isContainer ? children : parent.attr('id');
            }
        });

        $list.on('click', '.delete-lesson-btn', function(e) {
            if (!confirm('删除课时的同时会删除课时的资料、测验。\n您真的要删除该课时吗？')) {
                return ;
            }
            var $btn = $(e.currentTarget);
            $.post($(this).data('url'), function(response) {
                $btn.parents('.item-lesson').remove();
                sortList($list);
                Notify.success('课时已删除！');
            }, 'json');
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

        $list.on('click', '.replay-lesson-btn', function(e) {
            if (!confirm('您真的要录制回放吗？')) {
                return ;
            }
            $.post($(this).data('url'), function(html) {
                if(html.error){
                    if(html.error.code == 10019)
                        Notify.danger("录制失败，直播时您没有进行录制！");
                    else
                        Notify.danger("录制失败！");
                }else{
                    var id = '#' + $(html).attr('id');
                    $(id).replaceWith(html);
                    Notify.success('课时已录制！');
                }
            });
        });

        $list.on('click', '.publish-lesson-btn', function(e) {
            var $btn = $(e.currentTarget);
            $.post($(this).data('url'), function(html) {
                var id = '#' + $(html).attr('id');
                $(id).replaceWith(html);
                $(id).find('.btn-link').tooltip();
                Notify.success('课时发布成功！');
            });
        });

        $list.on('click', '.unpublish-lesson-btn', function(e) {
            var $btn = $(e.currentTarget);
            $.post($(this).data('url'), function(html) {
                var id = '#' + $(html).attr('id');
                $(id).replaceWith(html);
                $(id).find('.btn-link').tooltip();
                Notify.success('课时已取消发布！');
            });
        });

        Sticky('.lesson-manage-panel .panel-heading', 0, function(status){
            if (status) {
                var $elem = this.elem;
                $elem.addClass('sticky');
                $elem.width($elem.parent().width() - 10);
            } else {
                this.elem.removeClass('sticky');
                this.elem.width('auto');
            }
        });

        $("#course-item-list .item-actions .btn-link").tooltip();

    };

});