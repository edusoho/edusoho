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

      var $table = $('#vipLevelTable');
      $('[name="vipOrder"]').on('change', function () {
        $.get($table.data('url'), {seq:$(this).val()}, function (res) {
            $table.find('tr').remove();
            for (let i = 0; i < res.length; i++) {
              $table.append('<tr style="border-top: 1px solid #ddd;"><td style="padding: 10px 0 10px 5px">'+res[i].name+'</td></tr>');
            }
        })
      });

      $('[name="vipList"]').on('change', function () {
        if ($(this).val() == 'hidden'){
          $('.vip-list-block').addClass('hidden');
        }else{
          $('.vip-list-block').removeClass('hidden')
        }
      });

    };
});