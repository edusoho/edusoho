define(function(require, exports, module){
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
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

            var $form = $("#courseware-form");
            $modal = $form.parents('.modal');
            var validator = _initValidator($form, $modal);

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
                  element: '#tag-main-knowlege-tree-chooser',
                  sourceUrl: "/admin/knowledge/getTreeList?categoryId="+$categoryId,
                  queryUrl: '/admin/knowledge/choosered',
                  matchUrl: '/admin/tagset/match?q={{query}}',
                  maxTagNum: 1,
                  choosedTags: $mainKnowledgeId
                });

                chooserTreeForMainKnowlege.on('change', function(tags) {

                  $.each(tags,function(i,item){
                    mainKnowledgeId = item.id;
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
              element: '#tag-releated-knowlege-tree-chooser',
              sourceUrl: "/admin/knowledge/getTreeList?categoryId="+$categoryId,
              queryUrl: '/admin/knowledge/choosered',
              matchUrl: '/admin/tagset/match?q={{query}}',
              maxTagNum: 3,
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

        function _initValidator($form, $modal) {
                var validator = new Validator({
                element: '#courseware-form',
                failSilently: true,
                triggerType: 'change',
                autoSubmit: false,
                onFormValidated: function(error, results, $form) {

                    if (error) {
                        return false;
                    }
                    $('#courseware-operate-btn').button('submiting').button('loading').addClass('disabled');
                    tagIds = tagIds.join(",");
                    relatedKnowledgeIds = relatedKnowledgeIds.join(",");
                    $.post($form.attr('action'), $form.serialize()+'&tagIds='+tagIds+'&mainKnowledgeId='+mainKnowledgeId+'&relatedKnowledgeIds='+relatedKnowledgeIds, function(html) {
                        Notify.success('操作成功！');
                        window.location.reload();
                    });
                }
            });

            validator.addItem({
                element: '[name=url]',
                required: true,
                rule: 'url'
            });

            validator.addItem({
                element: '[name=mainKnowledgeId]',
                required: true
            });

            validator.addItem({
                element: '[name=relatedKnowledgeIds]',
                required: true
            });

            validator.addItem({
                element: '[name=tagIds]',
                required: true
            });

            validator.addItem({
                element: '[name=source]',
                required: true
            });

            return validator;
        }

        $('#import-courseware-url').click(function(){
            $url = $('#courseware-url-field').val();

            $re = /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/;
            if ($re.test($url)) {
                $(this).button('loading');
                $.post($(this).data('url'),{url:$url},function(result){
                    $('[data-role=courseware-title]').html(result.title);
                    $('#import-courseware-url').button('reset')
                });
            }
        });

        var tagModalChooser = new TagChooser({
            element: '#tag-chooser',
            sourceUrl: 'xxxx',
            multi: true,
            items: []
        });

        tagModalChooser.on('choosed', function(items) {
            var tagIds = [];
            for (var i = items.length - 1; i >= 0; i--) {
                tagIds[i] = items[i]['id'];
            };
            $('#courseware-tag-field').val(tagIds);
        });
    }
});
