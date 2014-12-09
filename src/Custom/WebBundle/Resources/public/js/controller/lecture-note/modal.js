define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Overlay = require('overlay');
    var Widget = require('widget');
    var TagTreeChooser = require('tag-tree-chooser');
    var TagChooser = require('tag-chooser');
    var TagChooserOverlay = require('tag-chooser-overlay');
    var TagTreeChooserOverlay = require('tag-tree-chooser-overlay');

    exports.run = function() {
        $('.method-form-group').on('change',function(){
            if($('.method-form-group').val() == 'essay'){
                $('#essay-search').removeClass('hide');
                $('#essay-table').removeClass('hide');
                $('#essay-material-search').addClass('hide');
                $('#essay-material-table').addClass('hide');
            } else {
                $('#essay-material-search').removeClass('hide');
                $('#essay-material-table').removeClass('hide');   
                $('#essay-search').addClass('hide');
                $('#essay-table').addClass('hide'); 
            }
        });

        $('#lecture-note-search').on('click',function(){
            $.get($('#lecture-note-search').data('url'), $('#lecture-note-form').serialize(), function(html) {
                $('#modal').html(html);
            });
            return false;
        });

        $('#lecture-note-list').on('click','.delete-btn',function(){
            var $deleteBtn =$(this);
            $.post($deleteBtn.data('url'),function(response){
                if(response.status){
                    $deleteBtn.parents('li').remove();
                    Notify.success(response.message);
                } else {
                    Notify.danger(response.message);
                }
            });
        });

        $('#essay-material-table').on('click','.essay-material-creat', function(){
            var materialBtn = $(this);
            $.post(materialBtn.data('url'),{id:materialBtn.data('id')},function(html){
                Notify.success('添加成功！');
                if ($('#lecture-note-list').children('div').hasClass('text-warning')){
                    $('#lecture-note-list').children('div').remove();
                }
                $('#lecture-note-list').append(html);

            }).error(function(){
                Notify.danger('添加失败！');
            });
        }); 

        $('#essay-table').on('click','.essay-creat', function(){
            var $btn = $(this);
            $.post($btn.data('url'),{id:$btn.data('id')},function(html){
                Notify.success('添加成功！');
                if ($('#lecture-note-list').children('div').hasClass('text-warning')){
                    $('#lecture-note-list').children('div').remove();
                }
                $('#lecture-note-list').append(html);

            }).error(function(){
                Notify.danger('添加失败！');
            });
        }); 

        var knowledgeOverlay = new TagTreeChooserOverlay({
            trigger: '.knowledge-search-trigger',
            element: $('#knowledges-search-overlay'),
            width: 400,
            align: {
                baseElement: $('#knowledges-search-group'),
                baseXY: [0, 36]
            },
            choosedTags: $('#lecture-note-form').find('input[name=knowledgeIds]').val().split(',')
        });

        knowledgeOverlay.on('change', function(tags, tagIds) {
            $('#lecture-note-form').find('input[name=knowledgeIds]').val(tagIds.join(','));
        });

        var tagOverlay = new TagChooserOverlay({
            trigger: '.tag-search-trigger',
            element: $('#tags-search-overlay'),
            width: 400,
            align: {
                baseElement: $('#tags-search-group'),
                baseXY: [0, 36]
            },
            choosedTags: $('#lecture-note-form').find('input[name=tagIds]').val().split(',')
        });

        tagOverlay.on('change', function(tags, tagIds) {
            $('#lecture-note-form').find('input[name=tagIds]').val(tagIds.join(','));
        });
    }

});