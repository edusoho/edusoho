define(function(require, exports, module) {
    exports.run = function() {
      var $themeEditContent = $('.js-theme-component');
      $("#save-btn").on('click', function(){
        var $form = $($(this).data('form'));
        var code = $form.data('code');
        var config = formSerialize($form);
        config.code = code;
        $themeEditContent.trigger('save_part_config', config);
        $("#modal").modal('hide');
      });
    };

    var formSerialize = function($form) {
      var config = {};
      $form.find('[name]').each(function(){
          var key = $(this).attr('name');
          var value = $(this).val();
          config[key] = value;
      });
      return config;
    }

});