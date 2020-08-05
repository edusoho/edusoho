define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  exports.run = function(options) {
    var validator = new Validator({
      element: '#exercise-recommend-form',
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return false;
        }
        $('#course-recommend-btn').button('submiting').addClass('disabled');
        $.post($form.attr('action'), $form.serialize(), function(html) {
          Notify.success(Translator.trans('admin.course.recommend_success_hint'));
          window.location.reload();
        }).error(function(){
          Notify.danger(Translator.trans('admin.course.recommend_fail_hint'));
        });
      }

    });

    validator.addItem({
      element: '[name="number"]',
      required: true,
      rule: 'integer min{min: 0} max{max: 10000}'
    });
  };

});

















