define(function(require,exports,module){

    var TagChooser = require('tag-chooser');
    var TagTreeChooser = require('tag-tree-chooser');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    var tagIds = [];
    var mainKnowledgeId = [];
    var relatedKnowledgeIds = [];
    var queryUrl = "";

    exports.run = function(){
        var $tagIds = [];
        var $mainKnowledgeId = [];
        var $relatedKnowledgeIds = [];
        var $form = $("#course-lesson-form");

        $modal = $form.parents('.modal');
        var validator = _initValidator($form, $modal);

         $form.on('change','[name=type]:checked',function(e){
            var lessonType = $(this).val();

            if (lessonType == 'courseware') {
            };
         });

         $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $activeRole = $(e.target).data('role')

            if ($activeRole == 'import-url') {
                _initTagChooser('#tag-chooser2');
                _initMainknowledgeTagChooser('#mainKnowledge-chooser2');
                _initRelatedknowledgeTagChooser('#relatedknowledges-chooser');
                _initImportBtn('#import-courseware-url');
            };
         });

        _init();
        _initSearchItems();

        function _init()
        {

            $value = $('[data-role=operate-flag]').val();

            if ($value > 0) {
                $('[data-role=placeholder]').parent().removeClass('hide');
                _hideCoursewaresPanel();
            }

            if ($value == 0) {
                _showCoursewaresPanel();
                _initTagChooser('#tag-chooser');
                _initMainknowledgeTagChooser('#mainKnowledge-chooser');
            };

            _trigger();
            _SearchBtnOnclick();

        }

        function _SearchBtnOnclick()
        {
            $('[data-role=search-coursewares-btn]').on("click",function(){

                var html = "";
                var $btn = $(this);

                if (typeof tagIds == 'string') {
                    tagIds = tagIds.split(',');
                };
                if (tagIds.length > 1) {
                    tagIds = tagIds.join(",");
                };

                if (tagIds.length == 1 ) {
                    tagIds= tagIds[0];
                };

                $keyword = $('[name=keyword]').val();
                $btn.text('搜索中...');

                _searchItems();

            });
        }

        function _initImportBtn(element)
        {
            $(element).on('click',function(){
                $url = $('#courseware-url-field').val();
                $re = /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/;
                if ($re.test($url)) {
                    $(this).button('loading');
                    $.post($(this).data('url'),{url:$url},function(result){
                        if (result.status) {
                            $('[data-role=courseware-title]').html('<p class=\'text-danger\'>此URL有误，请检查</p>');
                            $(element).button('reset');
                            return;
                        };
                        $('[data-role=courseware-title]').html(result.title);
                        $(element).button('reset');
                    });
                }
            });
        }

        function _initSearchItems()
        {
            _searchItems();
        }

        function _searchItems()
        {
            var html = "";
            var $keyword = $('[name=keyword]').val();
            var $btn = $('[data-role=search-coursewares-btn]');

            $.get($btn.data('url'),{mainKnowledgeId:mainKnowledgeId,tagIds:tagIds,keyword:$keyword},function(items){
                $btn.text('搜索');

                $.each(items,function(index,item){
                    html += "<tr style=\"cursor:pointer;\" data-role=\"search-courseware-item\" data-id=\""+item.id+"\"><td>"+item.title+"</td></tr>"
                });
                $('.search-result-table').find('tbody').html(html);
                $('[data-role=search-courseware-item]').on('click',function(){
                    _hideCoursewaresPanel();
                    $('[data-role=placeholder]').attr("data-id",$(this).data('id'));
                    $('[data-role=placeholder]').html($(this).find('td').text());
                    _showPlaceholder();
                });

                $('[data-role=trigger]').on('click',function(){
                    _hidePlaceholder();
                    _showCoursewaresPanel();
                });
            });
        }

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

                    $activeRole = _getActiveRole();

                    if ($activeRole == 'coursewares-chooser') {
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
                    };

                    if ($activeRole == 'import-url') {
                        if (mainKnowledgeId.length == 0) {
                            Notify.danger('主知识点不能为空');
                            $btn.button('reset');
                            return;
                        };

                        if (tagIds.length == 0) {
                            Notify.danger('标签不能为空');
                            $btn.button('reset');
                            return;
                        };

                        tagIds = tagIds.join(",");
                        relatedKnowledgeIds = relatedKnowledgeIds.join(",");

                        $btn.button('submiting').button('loading').addClass('disabled');
                        $.post($form.attr('action'), $form.serialize()+'&tagIds='+tagIds+'&mainKnowledgeId='+mainKnowledgeId+'&relatedKnowledgeIds='+relatedKnowledgeIds+'&tab=importUrl', function(response) {
                                Notify.success('操作成功！');
                                window.location.reload();
                        }).error(function(){
                                Notify.danger('操作失败！');
                        });
                    };
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

        function _getActiveRole()
        {
            $activeItem = " ";
            $coursewareTab = $('#coursewareTab');
            $coursewareTab.find('li').each(function(index,item){
                if ($(item).hasClass('active')) {
                    $activeItem = $(item);
                    $activeItem = $activeItem.find('a').data('role');
                };
            });

            return $activeItem;
        }

        function _initTagChooser(element)
        {
            var chooser = new TagChooser({
                element: element,
                queryUrl: $(element).data('queryUrl'),
                matchUrl: $(element).data('matchUrl'),
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

        function _initMainknowledgeTagChooser(element)
        {
            $categoryId = $('[data-role=categoryId]').val();
            var chooserTreeForMainKnowlege = new TagTreeChooser({
                element: element,
                sourceUrl: $(element).data('sourceUrl'),
                queryUrl: $(element).data('queryUrl'),
                matchUrl: $(element).data('matchUrl'),
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

        function _initRelatedknowledgeTagChooser(element)
        {
            var chooserTreeForRelatedKnowlege = new TagTreeChooser({
                 element: element,
                 sourceUrl: $(element).data('sourceUrl'),
                 queryUrl: $(element).data('queryUrl'),
                 matchUrl: $(element).data('matchUrl'),
                 maxTagNum: 15,
                 // choosedTags: $relatedKnowledgeIds
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

        function _trigger()
        {
            $('[data-role=trigger]').on('click',function(){
                _hidePlaceholder();
                _showCoursewaresPanel();
                // _initTagChooser('#tag-chooser');
                // _initMainknowledgeTagChooser('#mainKnowledge-chooser');
            });
        }

        function _showPlaceholder()
        {
            $('[data-role=placeholder]').parent().removeClass('hide');
        }

        function _hidePlaceholder()
        {
            $('[data-role=placeholder]').parent().addClass('hide');
        }

        function _showCoursewaresPanel()
        {
            $('.courseware-chooser').removeClass('hide');
        }

        function _hideCoursewaresPanel()
        {
            $('.courseware-chooser').addClass('hide');
        }
    }
});