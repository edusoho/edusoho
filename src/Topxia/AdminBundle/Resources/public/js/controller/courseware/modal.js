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
                  $tagIds = $tagIds.split(',');
                }; 

                var chooser = new TagChooser({
                    element: '#tag-chooser',
                    sourceUrl: $('#tag-chooser').data('sourceUrl'),
                    queryUrl: $('#tag-chooser').data('queryUrl'),
                    matchUrl: $('#tag-chooser').data('matchUrl'),
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
                    element: '#mainknowledge-chooser',
                    sourceUrl: $('#knowledges-search').data('sourceUrl'),
                    queryUrl: $('#knowledges-search').data('queryUrl'),
                    matchUrl: $('#knowledges-search').data('matchUrl'),
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
                $relatedKnowledgeIds = $relatedKnowledgeIds.split(',');
            };

            var chooserTreeForRelatedKnowlege = new TagTreeChooser({
              element: '#relatedknowledges-chooser',
              sourceUrl: $('#knowledges-search').data('sourceUrl'),
              queryUrl: $('#knowledges-search').data('queryUrl'),
              matchUrl: $('#knowledges-search').data('matchUrl'),
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

                    var $btn = $('#courseware-operate-btn');
                    $btn.button('submiting').button('loading').addClass('disabled');

                    if (mainKnowledgeId == "") {
                        Notify.danger('主知识点不能为空');
                        $btn.button('reset');
                        return;
                    };

                    if (tagIds == "" || tagIds.length == 0) {
                        Notify.danger('标签不能为空');
                        $btn.button('reset');
                        return;
                    };

                    tagIds = tagIds.join(",");
                    relatedKnowledgeIds = relatedKnowledgeIds.join(",");
                    $.post($form.attr('action'), $form.serialize()+'&tagIds='+tagIds+'&mainKnowledgeId='+mainKnowledgeId+'&relatedKnowledgeIds='+relatedKnowledgeIds, function(response) {
                        if (response.error){
                            Notify.danger(response.message);
                            $('#courseware-operate-btn').removeClass('disabled');
                        } else {
                            Notify.success('操作成功！');
                            window.location.reload();
                        }
                    });
                }
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

    }
});
