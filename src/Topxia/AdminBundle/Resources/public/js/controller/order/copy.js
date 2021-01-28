define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  exports.run = function (){
    $('#copy').click(function(){
      $("#copy-content").select();
      console.log($("#copy-content").val());
      document.execCommand("Copy");
      Notify.success(Translator.trans('notify.copy_succeed.message'));
    });
  }
});