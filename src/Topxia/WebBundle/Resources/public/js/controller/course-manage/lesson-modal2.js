define(function(require,exports,module){

    var TagChooser = require('tag-chooser');
    var TagTreeChooser = require('tag-tree-chooser');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    var tagIds = [];
    var mainKnowledgeId = [];
    var tagIdsForTestpaper = [];
    var mainKnowledgeIdForTestpaper = [];
    var relatedKnowledgeIds = [];
    var queryUrl = "";

    exports.run = function(){
        var $tagIds = [];
        var $mainKnowledgeId = [];
        var $relatedKnowledgeIds = [];
        var $form = $("#course-lesson-form");
        var $testpaperFlag = 0;
        var $coursewareFlag = 0;
        var $essayFlag = 0;

        $modal = $form.parents('.modal');
         $form.on('change','[name=type]:checked',function(e){
            var lessonType = $(this).val();
            if (lessonType == 'courseware') {
                _hideEssayChooserModule();
                _hideTestpaperChooserModule();
                _showCoursewareChooserModule();
                if ($coursewareFlag == 0) {
                    $coursewareFlag = 1;
                    validator = _initValidator($form, $modal);
                };
            };

            if (lessonType == 'essay') {
                _hideCoursewareChooserModule();
                _hideTestpaperChooserModule();
                _showEssayChooserModule();
                if ($essayFlag == 0) {
                    $essayFlag = 1;
                    validator = _initValidatorForEssay($form, $modal);
                };
                _searchEssaysBtnOnclick();
                _searchEssayItems();
                _triggerForEssay();
            };

            if (lessonType == 'testpaper') {
                console.log('testpaper')
                _hideCoursewareChooserModule();
                _hideEssayChooserModule();
                _showTestpaperChooserModule();


                if ($testpaperFlag == 0) {
                    $testpaperFlag = 1;
                    validator = _initValidatorForTestpaper($form, $modal);
                    _initTagChooserForTestpaper('#tag-chooser-for-testpaper');
                    _initMainknowledgeTagChooserForTestpaper('#mainKnowledge-chooser-for-testpaper');
                };
                _searchTestpapersBtnOnclick();
                _searchTestpaperItems();
                _triggerForTestpaper();
            };
         });

         $flag = 0;
         $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $activeRole = $(e.target).data('role')

            if ($activeRole == 'import-url' && $flag == 0) {
                $flag = 1;
                _initTagChooser('#tag-chooser2');
                _initMainknowledgeTagChooser('#mainKnowledge-chooser2');
                _initRelatedknowledgeTagChooser('#relatedknowledges-chooser');
                _initImportBtn('#import-courseware-url');
            };
         });
         var $lessonType = $('[data-role=lesson-type]').val();
console.log($lessonType)
         if ($lessonType == 'essay') {
            _initEssay();
            _searchEssayItems();
         } else if($lessonType == 'testpaper') {
         } else {
            _initCourseware();
            _initSearchItems();
         }

        function _initEssay()
         {
            var validator = _initValidatorForEssay($form, $modal);
            console.log('okk')
            var $value = $('[data-role=operate-flag-essay]').val();

            _hideCoursewaresPanel();
            _hideCoursewareChooserModule();
            _showEssayChooserModule();
            if ($value > 0) {
                _hideEssaysPanel();
                _showEssayPlaceholder();
                _searchEssaysBtnOnclick();
                _triggerForEssay();
            };
            console.log($value)
         }


        function _initCourseware()
        {
            $('[data-role=loading]').addClass('hide');
            _showCoursewareChooserModule();
            var $value = $('[data-role=operate-flag]').val();
            validator = _initValidator($form, $modal);
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
            _searchBtnOnclick();


        }

        function _searchEssaysBtnOnclick()
        {
            $('[data-role=search-essays-btn]').on("click",function(){
                var $btn = $(this);

                $btn.text('搜索中...');

                _searchEssayItems();
            });
        }

        function _searchTestpapersBtnOnclick()
        {
            $('[data-role=search-testpapers-btn]').on("click",function(){
                var html = '';
                var $btn = $(this);

                if (typeof tagIdsForTestpaper == 'string') {
                    tagIdsForTestpaper = tagIdsForTestpaper.split(",");
                };

                if (tagIdsForTestpaper.length > 1) {
                    tagIdsForTestpaper = tagIdsForTestpaper.join(",");
                };

                if (tagIdsForTestpaper.length == 1) {
                    tagIdsForTestpaper = tagIdsForTestpaper[0];
                };

                $btn.text('搜索中...');

                _searchTestpaperItems();
            });
        }

        function _searchBtnOnclick()
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


        function _searchEssayItems()
        {
            var html = "";
            var $keyword = $('[name=essayKeyword]').val();
            $btn = $("[data-role=search-essays-btn]");

            $.get($btn.data('url'),{keyword:$keyword},function(items){
                $btn.text('搜索');

                $.each(items,function(index,item){
                    html += "<tr style=\"cursor:pointer\" data-role=\"search-essay-item\" data-id=\""+item.id+"\"><td>"+item.title+"</td></tr>";
                });
                $('.search-essay-result-table').find('tbody').html(html);
                $('[data-role=search-essay-item]').on('click',function(){
                    _hideEssaysPanel();
                    $('[data-role=essay-placeholder]').attr("data-id",$(this).data('id'));
                    $('[data-role=essay-placeholder]').html($(this).find('td').text());
                    _showEssayPlaceholder();
                });
            });
        }

        function _searchTestpaperItems()
        {
            var html = "";
            var $keyword = $('[name=testpaperKeyword]').val();
            var $categoryId = $('[name=categoryId]').val();
            $btn = $("[data-role=search-testpapers-btn]");

            $.get($btn.data('url'),{categoryId:$categoryId,keyword:$keyword,tagIds:tagIdsForTestpaper,mainKnowledgeId:mainKnowledgeIdForTestpaper},function(items){
                $btn.text('搜索');

                $.each(items,function(index,item){
                    html += "<tr style=\"cursor:pointer\" data-role=\"search-testpaper-item\" data-id=\""+item.id+"\"><td>"+item.name+"</td></tr>";
                });
                $('.search-testpaper-result-table').find('tbody').html(html);
                $('[data-role=search-testpaper-item]').on('click',function(){
                    _hideTestpapersPanel();
                    $('[data-role=testpaper-placeholder]').attr("data-id",$(this).data('id'));
                    $('[data-role=testpaper-placeholder]').html($(this).find('td').text());
                    _showTestpaperPlaceholder();
                });
            });
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

        function _initValidatorForEssay($form, $modal)
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
                    var $essayId = $('[data-role=essay-placeholder]').data('id');

                    if (!$essayId) {
                        Notify.danger('请选择文章');
                        return false;
                    };

                    $btn.button('submiting').button('loading').addClass('disabled');

                    $.post($form.attr('action'), $form.serialize()+'&mediaId='+$essayId, function(response) {
                            Notify.success('操作成功！');
                            window.location.reload();
                    }).error(function(){
                            Notify.danger('操作失败！');
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

        function _initValidatorForTestpaper($form, $modal)
        {
            console.log('_initValidatorForTestpaper')
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
                    var $testpaperId = $('[data-role=testpaper-placeholder]').data('id');

                    if (!$testpaperId) {
                        Notify.danger('请选择试卷');
                        return false;
                    };

                    $btn.button('submiting').button('loading').addClass('disabled');
                    $.post($form.attr('action'), $form.serialize()+'&mediaId='+$testpaperId, function(response) {
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

        function _initValidator($form, $modal)
        {
            console.log('_initValidator')
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
console.log($activeRole)
                    if ($activeRole == 'coursewares-chooser') {
                        $coursewareId = $('[data-role=placeholder]').data('id');
                        if (!$coursewareId) {
                            var lessonType  = $('#lesson-type').find('[name=type]:checked').val();

                            if (lessonType == 'courseware') {
                                Notify.danger('请选择课件');
                            };
                            return false;
                        };

                        $btn.button('submiting').button('loading').addClass('disabled');
console.log('hasActice')
                        $.post($form.attr('action'),$form.serialize()+'&mediaId='+$coursewareId,function(){
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
                maxTagNum: 15
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

        function _initTagChooserForTestpaper(element)
        {
            var chooser = new TagChooser({
                element: element,
                queryUrl: $(element).data('queryUrl'),
                matchUrl: $(element).data('matchUrl'),
                maxTagNum: 15
            });

            chooser.on('change', function(tags) {
                var tagIdsTemp = [];
                $.each(tags,function(i,item){
                    tagIdsTemp.push(item.id)
                })
                tagIdsForTestpaper = tagIdsTemp;
            });
            chooser.on('existed', function(existTag){
            });
        }

        function _initMainknowledgeTagChooser(element)
        {
            console.log('_initMainknowledgeTagChooser')
            $categoryId = $('[data-role=categoryId]').val();
            var chooserTreeForMainKnowlege = new TagTreeChooser({
                element: element,
                sourceUrl: $(element).data('sourceUrl'),
                queryUrl: $(element).data('queryUrl'),
                matchUrl: $(element).data('matchUrl'),
                maxTagNum: 1
            });

            chooserTreeForMainKnowlege.on('change', function(tags) {

              $.each(tags,function(i,item){
                mainKnowledgeId = item.id;
              });
            });

            chooserTreeForMainKnowlege.on('existed', function(existTag){
            });
        }

        function _initMainknowledgeTagChooserForTestpaper(element)
        {
            console.log('_initMainknowledgeTagChooserForTestpaper')
            $categoryId = $('[data-role=categoryId]').val();
            var chooserTreeForMainKnowlege = new TagTreeChooser({
                element: element,
                sourceUrl: $(element).data('sourceUrl'),
                queryUrl: $(element).data('queryUrl'),
                matchUrl: $(element).data('matchUrl'),
                maxTagNum: 1
            });

            chooserTreeForMainKnowlege.on('change', function(tags) {

              $.each(tags,function(i,item){
                mainKnowledgeIdForTestpaper = item.id;
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
                 maxTagNum: 15
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
            });
        }

        function _triggerForEssay()
        {
            $('[data-role=essay-trigger]').on('click',function(){
                _hideEssayPlaceholder();
                _showEssaysPanel();
            });
        }

        function _triggerForTestpaper()
        {
            $('[data-role=testpaper-trigger]').on('click',function(){
                _hideTestpaperPlaceholder();
                _showTestpapersPanel();
            });
        }

        function _hideTestpapersPanel()
        {
            $('.testpaper-chooser').addClass('hide');
        }

        function _showTestpapersPanel()
        {
            $('.testpaper-chooser').removeClass('hide');
        }

        function _showTestpaperChooserModule()
        {
            $('.testpaper-chooser-module').removeClass('hide');
        }

        function _hideTestpaperChooserModule()
        {
            $('.testpaper-chooser-module').addClass('hide');
        }

        function _showEssayChooserModule()
        {
            $('.essay-chooser-module').removeClass('hide');
        }

        function _hideEssayChooserModule()
        {
            $('.essay-chooser-module').addClass('hide');
        }

        function _showCoursewareChooserModule()
        {
            $('.courseware-chooser-module').removeClass('hide');
        }

        function _hideCoursewareChooserModule()
        {
            $('.courseware-chooser-module').addClass('hide');
        }

        function _showPlaceholder()
        {
            $('[data-role=placeholder]').parent().removeClass('hide');
        }

        function _hidePlaceholder()
        {
            $('[data-role=placeholder]').parent().addClass('hide');
        }

        function _showTestpaperPlaceholder()
        {
            $('[data-role=testpaper-placeholder]').parent().removeClass('hide');
        }

        function _hideTestpaperPlaceholder()
        {
            $('[data-role=testpaper-placeholder]').parent().addClass('hide');
        }

        function _showEssayPlaceholder()
        {
            $('[data-role=essay-placeholder]').parent().removeClass('hide');
        }

        function _hideEssayPlaceholder()
        {
            $('[data-role=essay-placeholder]').parent().addClass('hide');
        }

        function _showCoursewaresPanel()
        {
            $('.courseware-chooser').removeClass('hide');
        }

        function _hideCoursewaresPanel()
        {
            $('.courseware-chooser').addClass('hide');
        }

        function _hideEssaysPanel()
        {
            $('.essay-chooser').addClass('hide');
        }

        function _showEssaysPanel()
        {
            $('.essay-chooser').removeClass('hide');
        }
    }
});