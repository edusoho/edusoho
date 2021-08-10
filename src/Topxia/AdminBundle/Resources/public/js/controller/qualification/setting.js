define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
      let $form = $('#qualification-form');

      $('.qualification-submit').on('click', function () {
        if ($("input[name='qualification_enabled']:checked").val() == '0') {
          if (!confirm(Translator.trans('admin_v2.teacher_qualification.setting_required'))) {
            return false;
          }
        }

        $.post($form.attr('action'), $form.serialize(), function(data){
          Notify.success(Translator.trans('site.save_success_hint'));
          window.location.reload();
        });
      });

      $("input[name='qualification_enabled']").change(function(){
        if($(this).val()=='0') {
          $(".qualification_tip").addClass('hidden');
        }else{
          $(".qualification_tip").removeClass('hidden');
        }
      })
    };

});