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

    var CoursewareChooser = require('../media-chooser/courseware-chooser.js');
    var EssayChooser = require('../media-chooser/essay-chooser.js');
    var TestpaperChooser = require('../media-chooser/testpaper-chooser');

    exports.run = function(){
        var $form = $("#course-lesson-form");
        var $testpaperFlag = 0;
        var $coursewareFlag = 0;
        var $essayFlag = 0;

        var coursewareChooser = new CoursewareChooser({
            type:'courseware',
            element:'.courseware-chooser-module'
        });

        var essayChooser = new EssayChooser({
            type:'essay',
            element:'.essay-chooser-module'
        });

        var testpaperChooser = new TestpaperChooser({
            type:'testpaper',
            element:'.testpaper-chooser-module'
        });

        $modal = $form.parents('.modal');
         $form.on('change','[name=type]:checked',function(e){
            var lessonType = $(this).val();
            if (lessonType == 'courseware') {
                _initCourseware();
            };

            if (lessonType == 'essay') {
                _initEssay();
            };

            if (lessonType == 'testpaper') {
                _initTestpaper();
            };
         });

         $importFlag = 0;
         $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $activeRole = $(e.target).data('role')

            if ($activeRole == 'import-url' && $importFlag == 0) {
                $importFlag = 1;
                _initImportUrl();
            };
         });

         var $lessonType = $('[data-role=lesson-type]').val();
         if ($lessonType == 'essay') {
            _initEditEssay();
            _searchEssayItems();
         } else if($lessonType == 'testpaper') {
         } else {
            _initEditCourseware();
            _initSearchCoursewares();
         }

        function _initImportUrl()
        {
            _initTagChooser('#tag-chooser2');
            _initMainknowledgeTagChooser('#mainKnowledge-chooser2');
            _initRelatedknowledgeTagChooser('#relatedknowledges-chooser');
        }

        function _initCourseware()
        {
            essayChooser.hideModule();
            testpaperChooser.hideModule();
            coursewareChooser.showModule();

            if ($coursewareFlag == 0) {
                $coursewareFlag = 1;
                validator = _initValidator($form, $modal);
            };
        }

        function _initEssay()
        {
            coursewareChooser.hideModule();
            testpaperChooser.hideModule();
            essayChooser.showModule();

            if ($essayFlag == 0) {
                $essayFlag = 1;
                validator = _initValidatorForEssay($form, $modal);
            };
            _searchEssaysBtnOnclick();
            _searchEssayItems();
        }

        function _initTestpaper()
        {
            coursewareChooser.hideModule();
            essayChooser.hideModule();
            testpaperChooser.showModule();

            if ($testpaperFlag == 0) {
                $testpaperFlag = 1;
                validator = _initValidatorForTestpaper($form, $modal);
                _initTagChooserForTestpaper('#tag-chooser-for-testpaper');
                _initMainknowledgeTagChooserForTestpaper('#mainKnowledge-chooser-for-testpaper');
            };
            _searchTestpapersBtnOnclick();
            _searchTestpaperItems();
        }

        function _initEditEssay()
         {
            var validator = _initValidatorForEssay($form, $modal);
            var $value = $('[data-role=operate-flag-essay]').val();

            coursewareChooser.hidePanel();
            coursewareChooser.hideModule();
            essayChooser.showModule();

            if ($value > 0) {
                essayChooser.hidePanel();
                essayChooser.showPlaceholder();
                _searchEssaysBtnOnclick();
            };

         }

        function _initEditCourseware()
        {
            $('[data-role=loading]').addClass('hide');
            coursewareChooser.showModule();
            var $value = $('[data-role=operate-flag]').val();
            validator = _initValidator($form, $modal);

            if ($value > 0) {
                coursewareChooser.showPlaceholder();
                coursewareChooser.hidePanel();
            }

            if ($value == 0) {
                coursewareChooser.showPanel();
                _initTagChooser('#tag-chooser');
                _initMainknowledgeTagChooser('#mainKnowledge-chooser');
            };

            _searchCoursewaresBtnOnclick();
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
            var $btn = $('[data-role=search-testpapers-btn]');
            _baseSearchBtnOnclick($btn,'testpaper',tagIdsForTestpaper);
        }

        function _searchCoursewaresBtnOnclick()
        {
            var $btn = $('[data-role=search-coursewares-btn]');
            _baseSearchBtnOnclick($btn,'courseware',tagIds);
        }

        function _baseSearchBtnOnclick($btn,$type,$paras)
        {
            $btn.on("click",function(){

                var html = "";
                var $btn = $(this);

                if (typeof $paras == 'string') {
                    $paras = $paras.split(',');
                };
                if ($paras.length > 1) {
                    $paras = $paras.join(",");
                };

                if ($paras.length == 1 ) {
                    $paras= $paras[0];
                };

                $btn.text('搜索中...');

                if ($type == 'courseware') {
                    _searchCoursewareItems();
                };

                if ($type == 'testpaper') {
                    _searchTestpaperItems();
                };

            });
        }

        function _initSearchCoursewares()
        {
            _searchCoursewareItems();
        }

        function _searchEssayItems()
        {
            var $keyword = $('[name=essayKeyword]').val();
            essayChooser.searchItems({keyword:$keyword});
        }

        function _searchTestpaperItems()
        {
            var $keyword = $('[name=testpaperKeyword]').val();
            var $categoryId = $('[name=categoryId]').val();

            testpaperChooser.searchItems({categoryId:$categoryId,keyword:$keyword,tagIds:tagIdsForTestpaper,mainKnowledgeId:mainKnowledgeIdForTestpaper});
        }

        function _searchCoursewareItems()
        {
            var $keyword = $('[name=coursewareKeyword]').val();
            coursewareChooser.searchItems({mainKnowledgeId:mainKnowledgeId,tagIds:tagIds,keyword:$keyword});
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
                        var lessonType  = $('#lesson-type').find('[name=type]:checked').val();

                        if (lessonType == 'essay') {
                            Notify.danger('请选择文章');
                        };
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
                        var lessonType  = $('#lesson-type').find('[name=type]:checked').val();

                        if (lessonType == 'testpaper') {
                            Notify.danger('请选择试卷');
                        };
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

                    $activeRole = coursewareChooser.getActiveRole();
                    if ($activeRole == 'coursewares-chooser') {
                        $coursewareId = $('[data-role=courseware-placeholder]').data('id');
                        if (!$coursewareId) {
                            var lessonType  = $('#lesson-type').find('[name=type]:checked').val();

                            if (lessonType == 'courseware') {
                                Notify.danger('请选择课件');
                            };
                            return false;
                        };
                        $btn.button('submiting').button('loading').addClass('disabled');
                        $.post($form.attr('action'),$form.serialize()+'&mediaId='+$coursewareId,function(){
                            Notify.success('操作成功！');
                            window.location.reload();
                        });
                    };

                    if ($activeRole == 'import-url') {

                        $importUrl = $('#courseware-url-field').val();
                        if ($importUrl == '') {
                            Notify.danger('文件URL不能为空');
                            $btn.button('reset');
                            return;
                        };

                        $re = _getUrlRule();
                        if (!$re.test($importUrl)) {
                            Notify.danger('文件URL格式有误');
                            $btn.button('reset');
                            return;
                        }

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

        function _getUrlRule()
        {
            return /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/;
        }
    }
});