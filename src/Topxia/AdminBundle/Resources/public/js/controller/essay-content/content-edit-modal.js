define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Overlay = require('overlay');
    var Widget = require('widget');
    var TagTreeChooser = require('tag-tree-chooser');
    var TagChooser = require('tag-chooser');
    var TagChooserOverlay = require('tag-chooser-overlay');
    var TagTreeChooserOverlay = require('tag-tree-chooser-overlay');

    exports.run = function() { 
        $('#article-material-search').on('click',function(){
            $.get($('#article-material-search').data('url'), $('#message-search-form').serialize(), function(html) {
                $('#modal').html(html);
            });
            return false;
        });

        $('#content-edit-item-list').on('click','.content-edit',function(){
            var self = $(this);
            if (!confirm('您真的要替换该素材吗？')) {
                return ;
            }
            $.post(self.data('url'),{ materialId:self.data('articleMaterialId') },function(){
                Notify.success('替换成功！');
                window.location.reload();
            }).error(function(){
                Notify.danger('替换失败！');
            });
        });

        var knowledgeOverlay = new TagTreeChooserOverlay({
            trigger: '.knowledge-search-trigger',
            element: $('#knowledges-search-overlay'),
            width: 400,
            align: {
                baseElement: $('#knowledges-search-group'),
                baseXY: [0, 36]
            },
            choosedTags: $('#message-search-form').find('input[name=knowledgeIds]').val().split(',')
        });

        knowledgeOverlay.on('change', function(tags, tagIds) {
            $('#message-search-form').find('input[name=knowledgeIds]').val(tagIds.join(','));
        });

        var tagOverlay = new TagChooserOverlay({
            trigger: '.tag-search-trigger',
            element: $('#tags-search-overlay'),
            width: 400,
            align: {
                baseElement: $('#tags-search-group'),
                baseXY: [0, 36]
            },
            choosedTags: $('#message-search-form').find('input[name=tagIds]').val().split(',')
        });

        tagOverlay.on('change', function(tags, tagIds) {
            $('#message-search-form').find('input[name=tagIds]').val(tagIds.join(','));
        });
    }
});