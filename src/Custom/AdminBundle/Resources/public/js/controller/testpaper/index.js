define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Overlay = require('overlay');
    require("$");
    var TagTreeChooser = require('tag-tree-chooser');
    var TagChooser = require('tag-chooser');
    
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

        _initTagSearch();
        _initKnowledgeSearch();

    };

    function _initKnowledgeSearch() {
        var overlay = new Overlay({
          element: $('#knowledges-search-overlay'),
          width: 400,
          align: {
            baseElement: $('#knowledges-search-group'),
            baseXY: [0, 36]
          }
        });

        var chooser;

        $('.knowledge-search-trigger').click(function() {
          overlay.show();

          if (chooser) {
            return ;
          }
          chooser = new TagTreeChooser({
            element: '#knowledges-search',
                sourceUrl: $('#knowledges-search').data('sourceUrl'),
                queryUrl: $('#knowledges-search').data('queryUrl'),
                matchUrl: $('#knowledges-search').data('matchUrl'),
            maxTagNum: 4,
            // choosedTags: $("#testpaper-search-form").find('input[name=knowledgeIds]').val().split(','),
            alwaysShow: true
          });

          chooser.on('change', function(tags) {
            overlay.set('height', this.getHeight() + 70);
          });

        });

        overlay.$('.tag-search-confrim').click(function() {
          overlay.hide();
          var tags = chooser.get('choosedTags');
          var tagNames = [];
          var tagIds = [];
          $.each(tags, function(i, tag) {
            tagNames.push(tag.name);
            tagIds.push(tag.id);
          });
          var btnText = tagNames.length >0 ? tagNames.join(' ') : '全选';
          $('.knowledge-search-trigger').text(btnText);
          $("#testpaper-search-form").find('input[name=knowledgeIds]').val(tagIds.join(','));
        });

        overlay.$('.tag-search-cancel').click(function(){
          overlay.hide();
        });
    }

    function _initTagSearch() {
        var overlay = new Overlay({
          element: $('#tags-search-overlay'),
          width: 400,
          align: {
            baseElement: $('#tags-search-group'),
            baseXY: [0, 36]
          }
        });

        var chooser;

        $('.tag-search-trigger').click(function() {
          overlay.show();

          if (chooser) {
            return ;
          }

          chooser = new TagChooser({
            element: '#tags-search',
                sourceUrl: $('#tags-search').data('sourceUrl'),
                queryUrl: $('#tags-search').data('queryUrl'),
                matchUrl: $('#tags-search').data('matchUrl'),
            maxTagNum: 4,
            choosedTags: $("#testpaper-search-form").find('input[name=tagIds]').val().split(','),
            alwaysShow: true
          });

          chooser.on('change', function(tags) {
            overlay.set('height', this.getHeight() + 70);
          });

          overlay.set('height', chooser.getHeight() + 70);

        });

        overlay.$('.tag-search-confrim').click(function() {
          overlay.hide();
          var tags = chooser.get('choosedTags');
          var tagNames = [];
          var tagIds = [];
          $.each(tags, function(i, tag) {
            tagNames.push(tag.name);
            tagIds.push(tag.id);
          });
          var btnText = tagNames.length >0 ? tagNames.join(' ') : '全选';
          $('.tag-search-trigger').text(btnText);
          $("#testpaper-search-form").find('input[name=tagIds]').val(tagIds.join(','));
        });

        overlay.$('.tag-search-cancel').click(function(){
          overlay.hide();
        });
    }


});