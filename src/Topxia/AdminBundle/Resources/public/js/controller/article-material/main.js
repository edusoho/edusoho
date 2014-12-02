define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Overlay = require('overlay');
    var Widget = require('widget');
    var TagTreeChooser = require('tag-tree-chooser');
    var TagChooser = require('tag-chooser');
    var TagChooserOverlay = require('tag-chooser-overlay');
    var TagTreeChooserOverlay = require('tag-tree-chooser-overlay');

    exports.run = function() {


        var knowledgeOverlay = new TagTreeChooserOverlay({
            trigger: '.knowledge-search-trigger',
            element: $('#knowledges-search-overlay'),
            width: 400,
            align: {
                baseElement: $('#knowledges-search-group'),
                baseXY: [0, 36]
            },
            choosedTags: $("#article-material-search-form").find('input[name=knowledgeIds]').val().split(',')
        });

        knowledgeOverlay.on('change', function(tags, tagIds) {
            $("#article-material-search-form").find('input[name=knowledgeIds]').val(tagIds.join(','));
        });

        var tagOverlay = new TagChooserOverlay({
            trigger: '.tag-search-trigger',
            element: $('#tags-search-overlay'),
            width: 400,
            align: {
                baseElement: $('#tags-search-group'),
                baseXY: [0, 36]
            },
            choosedTags: $("#article-material-search-form").find('input[name=tagIds]').val().split(',')
        });

        tagOverlay.on('change', function(tags, tagIds) {
            $("#article-material-search-form").find('input[name=tagIds]').val(tagIds.join(','));
        });

        $('.delete-articleMaterial-btn').click(function(){

            if (!confirm('您真的要删除该课件吗？')) {
                return ;
            }

            var $btn = $(this);
            $.post($btn.data('url'),function(){
                Notify.success('删除成功！');
                window.location.reload();
            }).error(function(){
                Notify.danger('删除失败！');
            });
        });

        $('[data-role=batch-select]').click(function(){
            if ($(this).is(":checked") == true){
                $('[data-role=single-select]').prop('checked', true);
            } else {
               $('[data-role=single-select]').prop('checked', false);
            }
        });

        $('[data-role=batch-delete]').click(function(){

            var ids = [];
            $('[data-role=single-select]:checked').each(function(index,item) {
                ids.push($(item).data('coursewareId'));
            });

            if (ids.length == 0) {
                Notify.danger('未选中任何课件');
                return ;
            }

            if (!confirm('您真的要删除选中的课件吗？')) {
                return ;
            }
            var $btn = $(this);
            $.post($btn.data('url'),{ids:ids},function(){
                Notify.success('删除成功！');
                window.location.reload();
            }).error(function(){
                Notify.danger('删除失败！');
            });
        });
    };
});