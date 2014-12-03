define(function(require, exports, module){
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    var EditorFactory = require('common/kindeditor-factory');
    var TagChooser = require('tag-chooser');
    var TagTreeChooser = require('tag-tree-chooser');

    var tagIds = [];
    var relatedKnowledgeIds = [];
    var mainKnowledgeId = [];
    var queryUrl = "";

    exports.run = function(){
        var $tagIds = [];
        var $mainKnowledgeId = [];
        var $relatedKnowledgeIds = [];

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
              element: '#tagchooser',
              sourceUrl: $('#tagchooser').data('sourceUrl'),
              queryUrl: $('#tagchooser').data('queryUrl'),
              matchUrl: $('#tagchooser').data('matchUrl'),
              maxTagNum: 15,
              choosedTags: $tagIds
            });

            chooser.on('change', function(tags) {
              var tagIdsTemp = [];
              $.each(tags,function(i,item){
                  tagIdsTemp.push(item.id)
              })
              tagIds = tagIdsTemp;
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
              element: '#main-knowledges',
              sourceUrl: $('#main-knowledges').data('sourceUrl'),
              queryUrl: $('#main-knowledges').data('queryUrl'),
              matchUrl: $('#main-knowledges').data('matchUrl'),
              maxTagNum: 1,
              choosedTags: $mainKnowledgeId
            });

            chooserTreeForMainKnowlege.on('change', function(tags) {

              $.each(tags,function(i,item){
                mainKnowledgeId = [item.id];
              });
            });

            chooserTreeForMainKnowlege.on('existed', function(existTag){
            });
        }
         
        function _initRelatedknowledgeTagChooer()
        {
            if ($('[data-role=related-knowledge-ids]').length > 0) {
                $relatedKnowledgeIds = $('[data-role=related-knowledge-ids]').val();
                $relatedKnowledgeIds = [$relatedKnowledgeIds];
            };

            var chooserTreeForRelatedKnowlege = new TagTreeChooser({
                element: '#related-knowledges',
                sourceUrl: $('#related-knowledges').data('sourceUrl'),
                queryUrl: $('#related-knowledges').data('queryUrl'),
                matchUrl: $('#related-knowledges').data('matchUrl'),
                maxTagNum: 15,
                choosedTags: $relatedKnowledgeIds
            });

            chooserTreeForRelatedKnowlege.on('change', function(tags) {

              var relatedKnowledgeIdsTemp = [];
              $.each(tags,function(i,item){
                  relatedKnowledgeIdsTemp.push(item.id)
              })
              relatedKnowledgeIds = relatedKnowledgeIdsTemp;

            });

            chooserTreeForRelatedKnowlege.on('existed', function(existTag){
            });
        }

        var $form = $("#article-material-form");
        $modal = $form.parents('.modal');
        var validator = _initValidator($form, $modal);
        var $editor = _initEditorFields($form, validator);

        function _initEditorFields($form, validator) {
            var editor = EditorFactory.create('#richeditor-content-field', 'simple');
            validator.on('formValidate', function(elemetn, event) {
                editor.sync();
            });
            return editor;
        }

        function _initValidator($form, $modal) {
                var validator = new Validator({
                element: '#article-material-form',
                failSilently: true,
                triggerType: 'change',
                autoSubmit: false,
                onFormValidated: function(error, results, $form) {
                    if (error) {
                        return false;
                    }

                    var $btn = $('#article-material-operate-btn');
                    $btn.button('submiting').button('loading').addClass('disabled');

                    if (mainKnowledgeId == "") {
                        Notify.danger('主知识点不能为空');
                        $btn.button('reset');
                        return ;
                    };
                    if (tagIds == "") {
                        Notify.danger('标签不能为空');
                        $btn.button('reset');
                        return ;
                    };

                    if (relatedKnowledgeIds) {
                        relatedKnowledgeIds = relatedKnowledgeIds.join(",");
                    };

                    tagIds = tagIds.join(",");
                    $.post($form.attr('action'), $form.serialize()+'&tagIds='+tagIds+'&mainKnowledgeId='+mainKnowledgeId+'&relatedKnowledgeIds='+relatedKnowledgeIds, function(response) {
                        if (response.error){
                            Notify.danger(response.message);
                            $btn.button('reset');
                        } else {
                            Notify.success('操作成功！');
                            window.location.reload();
                        }
                    });

                }
            });

            validator.addItem({
                element: '[name=url]',
                required: true,
                rule: 'url'
            });

            validator.addItem({
                element: '[name=title]',
                required: true
            });

            validator.addItem({
                element: '[name=source]',
                required: true
            });

            return validator;
        }

    }
});