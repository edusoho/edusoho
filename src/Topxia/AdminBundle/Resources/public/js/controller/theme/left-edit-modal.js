define(function(require, exports, module) {

    require('jquery.serializeJSON');
    exports.run = function() {
      var $themeEditContent = $('#theme-edit-content');
      $("#save-btn").on('click', function(){
        var $form = $($(this).data('form'));
        var config = $form.serializeJSON();
        // 多选项为空时，置空
        var checkboxSetting = {};
        var $checkbox = $form.find("input[type='checkbox']");
        $checkbox.each(function(){
          checkboxSetting[$(this).attr('name')] = '';
        });
        config = $.extend(checkboxSetting, config);
        console.log(config);
        $themeEditContent.trigger('save_part_config', config);
        $("#modal").modal('hide');
      });
    };
});