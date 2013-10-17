define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');

	exports.run = function() {

        var validator = new Validator({
            element: '#course-chapter-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '[name="photofile[content]"]',
            required: true
        });

        validator.on('formValidated', function(error, msg, $form) {
          if (error) {
              return ;
          }

          $.post($form.attr('action'), $form.serialize(), function(html) {
              Notify.success('图片信息已保存');
              $form.parents('.modal').modal('hide');
          });

        });

	};
});