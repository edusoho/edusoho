define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  exports.run = function() {
    var $form = $('#tag-group-form');
    var $modal = $form.parents('.modal');
    var $table = $('#tag-group-table');
      $('#tag-group-create-btn').on('click', function(){
        $('#tag-group-create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize(), function(html){
          var $html = $(html);
          if ($table.find( '#' +  $html.attr('id')).length > 0) {
              $('#' + $html.attr('id')).replaceWith($html);
              Notify.success(Translator.trans('标签更新成功！'));
          } else {
              $table.find('tbody').prepend(html);
              Notify.success(Translator.trans('标签添加成功!'));
          }
          $modal.modal('hide');
        });
      });
    };
});