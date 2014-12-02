define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Overlay = require('overlay');
    var Widget = require('widget');
    var TagTreeChooser = require('tag-tree-chooser');
    var TagChooser = require('tag-chooser');
    var TagChooserOverlay = require('tag-chooser-overlay');
    var TagTreeChooserOverlay = require('tag-tree-chooser-overlay');

    exports.run = function() {

        var $container = $('#testpaper-list');
        require('../../util/batch-select')($container);
        require('../../util/batch-delete')($container);
        require('../../util/item-delete')($container);

        $('.test-paper-reset').on('click','',function(){
            if (!confirm('重置会清空原先的题目,确定要继续吗？')) {
                return ;
            }
            window.location.href=$(this).data('url');
        });

        var $table = $('#quiz-table');

        $table.on('click', '.open-testpaper, .close-testpaper', function() {
            var $trigger = $(this);
            var $oldTr = $trigger.parents('tr');

            if (!confirm('真的要' + $trigger.attr('title') + '吗？ 试卷发布后无论是否关闭都将无法修改或删除。')) {
                return ;
            }

            $.post($(this).data('url'), function(html){
                Notify.success($trigger.attr('title') + '成功！');

                var $tr = $(html);
                $oldTr.replaceWith($tr);
            }).error(function(){
                Notify.danger($trigger.attr('title') + '失败');
            });
        });

        var knowledgeOverlay = new TagTreeChooserOverlay({
            trigger: '.knowledge-search-trigger',
            element: $('#knowledges-search-overlay'),
            width: 400,
            align: {
                baseElement: $('#knowledges-search-group'),
                baseXY: [0, 36]
            }
        });

        knowledgeOverlay.on('change', function(tags, tagIds) {
            $("#testpaper-search-form").find('input[name=knowledgeIds]').val(tagIds.join(','));
        });

        var tagOverlay = new TagChooserOverlay({
            trigger: '.tag-search-trigger',
            element: $('#tags-search-overlay'),
            width: 400,
            align: {
                baseElement: $('#tags-search-group'),
                baseXY: [0, 36]
            }
        });

        tagOverlay.on('change', function(tags, tagIds) {
            $("#testpaper-search-form").find('input[name=tagIds]').val(tagIds.join(','));
        });

    };


});
