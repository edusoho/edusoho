define(function(require, exports, module) {
    exports.run = function() {
      alert();
      var $themeEditContent = $('#theme-edit-content');
      $('#maincolor').on("click", '.check-box', function(event){
        var $input = $(this).find('input');
        var name = $input.attr('name'), value =  $input.val();

        var config = {
          name: value
        }
        $themeEditContent.trigger('save_config', config);
      });
    }
});