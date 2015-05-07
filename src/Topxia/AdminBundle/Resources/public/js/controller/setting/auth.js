define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require('jquery.sortable');
  require('ckeditor');
  require('common/validator-rules').inject(Validator);
  var Notify = require('common/bootstrap-notify');
  exports.run = function() {

    // group: 'default'
    CKEDITOR.replace('user_terms_body', {
      toolbar: 'Simple',
      filebrowserImageUploadUrl: $('#user_terms_body').data('imageUploadUrl')
    });

    $(".register-list").sortable({
      'distance': 20
    });

    $("#show-register-list").hide();

    $("#hide-list-btn").on("click", function() {
      $("#show-register-list").hide();
      $("#show-list").show();
    });

    $("#show-list-btn").on("click", function() {
      $("#show-register-list").show();
      $("#show-list").hide();
    });

    $("input[name=register_protective]").change(function() {

      var type = $('input[name=register_protective]:checked').val();

      $('.register-help').hide();

      $('.' + type).show();

    });
    
    var old_selected_value = $("input[name='register_mode']:checked").val();
    $('input[name=register_mode]:radio').change(function(){
      var selected_value = $("input[name='register_mode']:checked").val();

      if(selected_value !='email_or_mobile'){
        old_selected_value = selected_value; //记住上一次的记录
      }else{
        if($('input[name=_cloud_sms]').val() !=1){
           $("input:radio[value="+old_selected_value+"]").prop("checked", true);
           Notify.danger("请先开启云短信功能！");
        }
      }
    })
  

    var validator = new Validator({
      element: '#auth-form'
    });

    validator.addItem({
      element: '[name="user_name"]',
      required: true
    });

  };


});