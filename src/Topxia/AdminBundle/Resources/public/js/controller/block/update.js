define(function(require, exports, module) {

  var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
      var $form = $('#block-form');
      var $modal = $form.parents('.modal');
      var $table = $('#block-table');

      var editor = EditorFactory.create('#blockContent', 'simple', {extraFileUploadParams:{group:'default'}, designMode:false});

      var validator = new Validator({
          element: $form,
          autoSubmit: false,
          onFormValidated: function(error, results, $form) {
            if (error) {
                return ;
            }

            $.post($form.attr('action'), $form.serialize(), function(response){
                if (response.status == 'ok') {
                    var $html = $(response.html);
                    if ($table.find( '#' +  $html.attr('id')).length > 0) {
                        $('#' + $html.attr('id')).replaceWith($html);
                        Notify.success('更新成功！');
                    } else {
                        $table.find('tbody').prepend(response.html);
                        Notify.success('提交成功!');
                    }
                    $modal.modal('hide');
                }
            }, 'json'); 
          }
      });

      $('.btn-recover-content').on('click', function(){
            var html = $(this).parents('tr').find('.data-role-content').text();
            editor.html(html);
      });
    };

});