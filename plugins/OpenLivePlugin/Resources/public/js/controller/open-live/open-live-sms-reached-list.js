define(function(require, exports, module) {
  require('jquery.form');
  var Notify = require('common/bootstrap-notify');

  exports.run = function (){
    var $form = $("#user-search-form");

    $('.data-list').on('click', '.pagination li', function() {
      var url = $(this).data('url');
      if (typeof(url) !== 'undefined') {
        $.post(url, $form.serialize(),function(data){
          $('.data-list').html(data);
        });
      }
    });

    var searchBtn = $('#search');
    searchBtn.on('click',function(){
      searchBtn.attr('disabled', true);
      searchBtn.html('查询中...');
      $.post($form.attr('action'), $form.serialize(), function(data){
        $('.data-list').html(data);
        searchBtn.html('查询');
        searchBtn.attr('disabled', false);
      }).error(function(){
        Notify.danger(Translator.trans('admin.setting.operation_fail_hint'));
        searchBtn.html('查询');
        searchBtn.attr('disabled', false);
      });
    });
  };
});