define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');

	exports.run = function() {
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
              if ($item.length) {
                  $item.replaceWith(html);
                  Notify.success('章节信息已保存');
              } else {
                  $("#course-item-list").append(html);
                  Notify.success('章节添加成功');
              }
              $(id).find('.btn-link').tooltip();
              $form.parents('.modal').modal('hide');
          });

        });

	};
});