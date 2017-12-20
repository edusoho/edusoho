define(function(require, exports, module) {

    require('jquery.serializeJSON');
    exports.run = function() {
      var $themeEditContent = $('.js-theme-component');
      $("#save-btn").on('click', function(){
        var $form = $($(this).data('form'));
        var config = $form.serializeJSON();
        $themeEditContent.trigger('save_part_config', config);
        $("#modal").modal('hide');
      });
    };
});