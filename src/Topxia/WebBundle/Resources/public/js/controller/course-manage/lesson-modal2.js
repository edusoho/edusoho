define(function(require,exports,module){

    var TagChooser = require('tag-chooser');
    var TagTreeChooser = require('tag-tree-chooser');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    var tagIds = [];
    var mainKnowledgeId = [];
    var queryUrl = "";

    exports.run = function(){
        var $tagIds = [];
        var $mainKnowledgeId = [];

        var $form = $("#course-lesson-form");
        $modal = $form.parents('.modal');
        var validator = _initValidator($form, $modal);

        _initTagChooer();
        _initMainknowledgeTagChooer();


        $('[data-role=search-coursewares-btn]').on("click",function(){
            console.log(tagIds)
            if (tagIds.length > 1) {
                tagIds = tagIds.join(",");
            };

            if (tagIds.length == 1 ) {
                tagIds= tagIds[0];
            };
            $keyword = $('[name=keyword]').val();
            var html = "";
            $.get($(this).data('url'),{mainKnowledgeId:mainKnowledgeId,tagIds:tagIds,keyword:$keyword},function(items){
                $.each(items,function(index,item){
                    html += "<tr style=\"cursor:pointer;\" data-role=\"search-courseware-item\" data-id=\""+item.id+"\"><td>"+item.title+"</td></tr>"
                });
                $('.search-result-table').find('tbody').html(html);
                $('[data-role=search-courseware-item]').on('click',function(){
                    $('.search-result').hide();
                    $('.courseware-chooser-uploader').hide();
                    $('[data-role=placeholder]').attr("data-id",$(this).data('id'));
                    $('[data-role=placeholder]').html($(this).find('td').text());
                    $('[data-role=placeholder]').parent().removeClass('hide');
                });

                $('[data-role=trigger]').on('click',function(){
                    $('[data-role=placeholder]').parent().addClass('hide');
                    $('.search-result').show();
                    $('.courseware-chooser-uploader').show();
                });
            });
        });


        function _initValidator($form, $modal)
        {
            var validator = new Validator({
                element:'#course-lesson-form',
                failSilently:true,
                triggerType:'change',
                autoSubmit:false,
                onFormValidated: function(error, results, $form){
                    if (error) {
                        return false;
                    }

                    var $btn = $('#lesson-operate-btn');
                    $coursewareId = $('[data-role=placeholder]').data('id');
                    if (!$coursewareId) {
                        Notify.danger('请选择课件');
                        return false;
                    };

                    $btn.button('submiting').button('loading').addClass('disabled');

                    $.post($form.attr('action'),$form.serialize()+'&coursewareId='+$coursewareId,function(){
                        Notify.success('操作成功！');
                        window.location.reload();
                    });
                }

            });

            validator.addItem({
                element:'[name=title]',
                required :true
            });

            validator.addItem({
                element:'[name=type]',
                required :true
            });

            return validator;
        }

        function _initTagChooer()
        {
            // if ($('[data-role=tag-ids]').length > 0) {
            //   $tagIds = $('[data-role=tag-ids]').val();
            //   $tagIds = $tagIds.split(',');
            // }; 

            var chooser = new TagChooser({
                element: '#tag-chooser',
                sourceUrl: $('#tag-chooser').data('sourceUrl'),
                queryUrl: $('#tag-chooser').data('queryUrl'),
                matchUrl: $('#tag-chooser').data('matchUrl'),
                maxTagNum: 15,
                // choosedTags: $tagIds
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
            $categoryId = $('[data-role=categoryId]').val();

            var chooserTreeForMainKnowlege = new TagTreeChooser({
                element: '#mainknowledge-chooser',
                sourceUrl: $('#mainknowledge-chooser').data('sourceUrl'),
                queryUrl: $('#mainknowledge-chooserh').data('queryUrl'),
                matchUrl: $('#mainknowledge-chooser').data('matchUrl'),
                maxTagNum: 1,
            });

            chooserTreeForMainKnowlege.on('change', function(tags) {

              $.each(tags,function(i,item){
                mainKnowledgeId = item.id;
              });
            });

            chooserTreeForMainKnowlege.on('existed', function(existTag){
            });
        }
    }
});