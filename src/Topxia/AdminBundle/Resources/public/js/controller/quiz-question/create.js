define(function(require, exports, module) {

    var QuestionBase = require('./creator/question-base');
    var Choice = require('./creator/question-choice');
    var Determine = require('./creator/question-determine');
    var Essay = require('./creator/question-essay');
    var Fill = require('./creator/question-fill');
    var TagChooser = require('tag-chooser');
    var TagTreeChooser = require('tag-tree-chooser');
    exports.run = function() {
        var type = $('#question-creator-widget').find('[name=type]').val().replace(/\_/g, "-");
        var QuestionCreator;
        switch (type) {
            case 'single-choice':
            case 'uncertain-choice':
            case 'choice':
                QuestionCreator = Choice;
                break;
            case 'determine':
                QuestionCreator = Determine;
                break;
            case 'essay':
                QuestionCreator = Essay;
                break;
            case 'fill':
                QuestionCreator = Fill;
                break;
            default:
                QuestionCreator = QuestionBase;
        }

        var creator = new QuestionCreator({
            element: '#question-creator-widget'
        });

            var tagIds = [];
            var relatedKnowledgeIds = [];
            var mainKnowledgeId = [];
            var queryUrl = "";
            var $tagIds = [];
            var $mainKnowledgeId = [];
            var $relatedKnowledgeIds = [];

            var $form = $("#question-create-form");
           _initTagChooer();
           _initMainknowledgeTagChooer();
           _initRelatedknowledgeTagChooer();

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
                 maxTagNum: 3,
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
                 console.log('existed');
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

                 console.log('change tags', tags);
                 $.each(tags,function(i,item){
                   mainKnowledgeId = item.id;
                 });
                 $('[data-role=main-knowledge-ids]').val(mainKnowledgeId);
               });

               chooserTreeForMainKnowlege.on('existed', function(existTag){
                 console.log('existed');
               });
           }
        
        function _initRelatedknowledgeTagChooer()
        {
           if ($('[data-role=related-knowledge-ids]').length > 0) {
               $relatedKnowledgeIds = $('[data-role=related-knowledge-ids]').val();
               $relatedKnowledgeIds = [$relatedKnowledgeIds];
           };

           var chooserTreeForRelatedKnowlege = new TagTreeChooser({
             element: '#tag-releated-knowlege-tree-chooser',
             sourceUrl: "/admin/knowledge/getTreeList?categoryId="+$categoryId,
             queryUrl: '/admin/knowledge/choosered',
             matchUrl: '/admin/tagset/match?q={{query}}',
             maxTagNum: 3,
             choosedTags: $relatedKnowledgeIds
           });

           chooserTreeForRelatedKnowlege.on('change', function(tags) {

             console.log('change tags', tags);

             var relatedKnowledgeIdsTemp = [];
             $.each(tags,function(i,item){
                 relatedKnowledgeIdsTemp.push(item.id)
             })
             relatedKnowledgeIds = relatedKnowledgeIdsTemp;
             $('[data-role=related-knowledge-ids]').val(relatedKnowledgeIdsTemp);
           });

           chooserTreeForRelatedKnowlege.on('existed', function(existTag){
             console.log('existed');
           });
        }
    };

});