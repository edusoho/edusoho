define(function(require, exports, module) {

    require('jquery.sortable');
    var Sticky = require('sticky');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        require('./header').run();

        var $list = $("#course-item-list").sortable({
            onDrop: function (item, container, _super) {
                _super(item, container);
                var data = $list.sortable("serialize").get();
                $.post($list.data('sortUrl'), {ids:data}, function(response){

                    $('.lesson-list').find('.item-lesson').each(function(index){

                        $(this).find('.sequence').text(index+1);

                    });
                    
                });
            },
            serialize: function(parent, children, isContainer) {
                return isContainer ? children : parent.attr('id');
            }
        });

        $("#course-item-list").on('click', '.delete-lesson-btn', function(e) {
            if (!confirm('删除课时的同时会删除课时的资料、测验。\n您真的要删除该课时吗？')) {
                return ;
            }
            var $btn = $(e.currentTarget);
            $.post($(this).data('url'), function(response) {
                $btn.parents('.item-lesson').remove();
                Notify.success('课时已删除！');
            }, 'json');
        });

        $("#course-item-list").on('click', '.delete-chapter-btn', function(e) {
            if (!confirm('您真的要删除该章节吗？')) {
                return ;
            }
            var $btn = $(e.currentTarget);
            $.post($(this).data('url'), function(response) {
                $btn.parents('.item-chapter').remove();
                Notify.success('章节已删除！');
            }, 'json');
        });

        $("#course-item-list").on('click', '.publish-lesson-btn', function(e) {
            var $btn = $(e.currentTarget);
            $.post($(this).data('url'), function(html) {
                var id = '#' + $(html).attr('id');
                $(id).replaceWith(html);
                $(id).find('.btn-link').tooltip();
                Notify.success('课时发布成功！');
            });
        });

        $("#course-item-list").on('click', '.unpublish-lesson-btn', function(e) {
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