define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
       var Notify = require('common/bootstrap-notify');
       require('jquery.sortable');

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

        var validator = new Validator({
            element: '#course-chapter-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '#chapter-title-field',
            required: true
        });

        validator.on('formValidated', function(error, msg, $form) {
          if (error) {
              return ;
          }
          $('#course-chapter-btn').button('submiting').addClass('disabled');

          $.post($form.attr('action'), $form.serialize(), function(html) {

              var id = '#' + $(html).attr('id'),
                  $item = $(id);
              var $parent = $('#'+$form.data('parentid'));
              var $panel = $('.lesson-manage-panel');
              $panel.find('.empty').remove();
              if ($item.length) {
                  $item.replaceWith(html);
                  Notify.success('信息已保存');
              } else {
                 if($parent.length){
                  var add = 0;
                   $parent.nextAll().each(function(){
                     if($(this).hasClass('item-chapter  clearfix')){
                        $(this).before(html);
                        add = 1;
                        return false;
                      }
                     
                  });
                     if(add != 1 )
                        $("#course-item-list").append(html);
                   
                    var $list = $("#course-item-list");
                    sortList($list);
                   
                 }else{
                    $("#course-item-list").append(html);
                    $(".lesson-manage-panel").find('.empty').remove();
                 }

                  Notify.success('添加成功');

              }
              $(id).find('.btn-link').tooltip();
              $form.parents('.modal').modal('hide');
          });
	
        });

	};



});