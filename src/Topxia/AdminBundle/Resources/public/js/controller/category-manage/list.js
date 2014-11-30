define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var TagChooser = require('tag-chooser');
    var TagTreeChooser = require('tag-tree-chooser');

    require("$");
    exports.run = function() {
        var $container = $('#quiz-table-container');
        require('../../util/short-long-text')($container);
        require('../../util/batch-select')($container);
        require('../../util/batch-delete')($container);
        require('../../util/item-delete')($container);

        var $tagIds = [];
        var $relatedKnowledgeIds = [];
        var $mainKnowledgeId = [];
        var queryUrl = "";
        function _initTagChooer()
        {

            if ($('[data-role=tag-ids]').length > 0) {
                $tagIds = $('[data-role=tag-ids]').val();
                $tagIds = [$tagIds];
            }; 

            var chooser = new TagChooser({
              element: '#tag-chooser',
              sourceUrl: '/admin/tagset/get',
              queryUrl: '/admin/tags/Choosered',
              matchUrl: '/admin/tagset/match?q={{query}}',
              maxTagNum: 4,
              choosedTags: $tagIds
            });

            chooser.on('change', function(tags) {

              var tagIdsTemp = [];
              $.each(tags,function(i,item){
                  tagIdsTemp.push(item.id)
              })
              $('[data-role=tag-ids]').val(tagIdsTemp);
            });

            chooser.on('existed', function(existTag){
            });
        }

        function _initMainknowledgeTagChooer()
        {
            if ($('[data-role=main-knowledge-ids]').length > 0) {
                $mainKnowledgeId = $('[data-role=main-knowledge-ids]').val();
                $mainKnowledgeId = [$mainKnowledgeId];
            };
            $categoryId = $('[data-role=categoryId]').val();

            var chooserTreeForMainKnowlege = new TagTreeChooser({
              element: '#tag-main-knowlege-tree-chooser',
              sourceUrl: "/admin/knowledge/getTreeList?categoryId="+$categoryId,
              queryUrl: '/admin/knowledge/choosered',
              matchUrl: '/admin/tagset/match?q={{query}}',
              maxTagNum: 1,
              choosedTags: $mainKnowledgeId
            });
            chooserTreeForMainKnowlege.on('change', function(tags) {
              var temp = {};
              $.each(tags,function(i,item){
                temp = item.id;
              });
              $('[data-role=main-knowledge-ids]').val(temp);
            });

            chooserTreeForMainKnowlege.on('existed', function(existTag){
            });
        }

        _initMainknowledgeTagChooer();
        _initTagChooer();

    };

});