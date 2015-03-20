define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
         var sortList = function($list) {
            var data = $list.sortable("serialize").get();
            $.post($list.data('sortUrl'), {ids:data}, function(response){
                var lessonNum = chapterNum = unitNum = 0;

                $list.find('.item-lesson, .item-chapter').each(function() {
                    var $item = $(this);
                    if ($item.hasClass('item-lesson')) {
                        lessonNum ++;
                        $item.find('.number').text(lessonNum);
                    } else if ($item.hasClass('item-chapter-unit')) {
                        unitNum ++;
                        $item.find('.number').text(unitNum);
                    } else if ($item.hasClass('item-chapter')) {
                        chapterNum ++;
                        unitNum = 0;
                        $item.find('.number').text(chapterNum);
                    }

                });
            });
        };

        $('#lesson-mediaId-field').change(function() {
            var mediaId = $('#lesson-mediaId-field').find('option:selected').val();
            if (mediaId != '') {
                $('#lesson-title-field').val($('#lesson-mediaId-field').find('option:selected').text());
            } else {
                $('#lesson-title-field').val('');
            }
        });

        validator = new Validator({
            element: '#course-lesson-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '#lesson-mediaId-field',
            required: true,
            errormessageRequired: '请选择试卷'
        });

        validator.addItem({
            element: '#lesson-title-field',
            required: true
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }
            $('#course-testpaper-btn').button('submiting').addClass('disabled');

            var $panel = $('.lesson-manage-panel');
            $.post($form.attr('action'), $form.serialize(), function(html) {

                var id = '#' + $(html).attr('id'),
                    $item = $(id);
                var $parent = $('#'+$form.data('parentid'));    
                if ($item.length) {
                    $item.replaceWith(html);
                    Notify.success('试卷课时已保存');
                } else {
                    $panel.find('.empty').remove();
                    
                    if($parent.length){
                        var add = 0;
                        if($parent.hasClass('item-chapter  clearfix')){
                            $parent.nextAll().each(function(){
                            if($(this).hasClass('item-chapter  clearfix')){
                                $(this).before(html);
                                add = 1;
                                return false;
                             }
                          });
                            if(add !=1 ){
                                $("#course-item-list").append(html);
                                add = 1;
                            }
                            
                        }else{
                             $parent.nextAll().each(function(){
                                if($(this).hasClass('item-chapter  clearfix'))
                                    return false;
                                if($(this).hasClass('item-chapter item-chapter-unit clearfix')){
                                    $(this).before(html);
                                    add = 1;
                                    return false;
                             }
                          });
                        }
                         if(add != 1 ){
                              $("#course-item-list").append(html);
                         }   
                        var $list = $("#course-item-list");
                        sortList($list);
                     }else{
                      $("#course-item-list").append(html);
                 }
                    Notify.success('添加试卷课时成功');
                }
                $(id).find('.btn-link').tooltip();
                $form.parents('.modal').modal('hide');
            });

        });


    };
});