define(function(require, exports, module) {
    exports.run = function() {
      var $themeEditContent = $('#theme-edit-content');
      $('#maincolor').on("click", 'input', function(event){
        var $input = $(this);
        var name = $input.attr('name'), value =  $input.val(), config = {};
        config[name] = value;
        $themeEditContent.trigger('save_config', config);
      });
    }
});