define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Overlay = require('overlay');
    var Widget = require('widget');
    var TagTreeChooser = require('tag-tree-chooser');
    var TagChooser = require('tag-chooser');
    var TagChooserOverlay = require('tag-chooser-overlay');
    var TagTreeChooserOverlay = require('tag-tree-chooser-overlay');

    exports.run = function() {
        $('[data-role=batch-select]').click(function(){
            if ($(this).is(":checked") == true){
                $('[data-role=single-select]').prop('checked', true);
            } else {
               $('[data-role=single-select]').prop('checked', false);
            }
        });

        $('#article-material-search').on('click',function(){
            $.get($('#article-material-search').data('url'), $('#message-search-form').serialize(), function(html) {
                $('#modal').html(html);
            });
            return false;
        });

        $('#essay-content-creat-btn').on('click',function(){
            var ids = [];
            var chapterId = $(this).data('id');
            $('[data-role=single-select]:checked').each(function(index,item) {
                ids.push($(item).data('articleMaterialId'));
            });

            if (ids.length == 0) {
                Notify.danger('未选中任何课件');
                return ;
            }
            $.post($('#essay-content-creat-btn').data('url'),{materialIds:ids,chapterId:chapterId},function(){
                Notify.success('添加成功！');
                window.location.reload();
            }).error(function(){
                Notify.danger('添加失败！');
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