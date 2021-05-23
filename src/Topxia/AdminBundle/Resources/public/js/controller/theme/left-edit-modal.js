define(function(require, exports, module) {

    require('jquery.serializeJSON');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    exports.run = function() {
      var $themeEditContent = $('#theme-edit-content');
      $("#save-btn").on('click', function(){
        var $form = $($(this).data('form'));

        var config = $form.serializeJSON();
        if (config.categoryIds && config.categoryIds.length > 1){
          var arr = [];
          var brr = [];
          for(var i = 0; i < config.categoryIds.length; i++) {
            var item = config.categoryIds[i].categoryId;
            if (arr.indexOf(item) == -1 && String(item) != '0'){
              arr.push(item);
              brr.push({
                categoryId: item
              });
            }
          }
          config.categoryIds = brr;
        }

        // 多选项为空时，置空
        var checkboxSetting = {};
        var $checkbox = $form.find("input[type='checkbox']");
        $checkbox.each(function(){
          checkboxSetting[$(this).attr('name')] = '';
        });
        config = $.extend(checkboxSetting, config);
        $themeEditContent.trigger('save_part_config', config);
        $("#modal").modal('hide');
      });

      var $table = $('#vipLevelTable');
      $('[name="vipOrder"]').on('change', function () {
        $.get($table.data('url'), {seq:$(this).val()}, function (res) {
            $table.find('tr').remove();
            for (var i = 0; i < res.length; i++) {
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

      $('[name="title"]').on('change', function (){
        if (getTitleLength($(this).val()) > 100){
          $('#save-btn').addClass('disabled');
          $('.titleTip').removeClass('hidden');
        }else{
          $('#save-btn').removeClass('disabled');
          $('.titleTip').addClass('hidden');
        }
      });

      getTitleLength = function(title) {
        if (title == null) return 0;
        if (typeof title != "string"){
          title += "";
        }
        return title.replace(/[^\x00-\xff]/g,"01").length;
      }

      $('#addCategory').on('click', function (event) {
        var selectCount = $('.articleCategory').length;
        if (selectCount >= 4){
          $('.categoriesTip').removeClass('hidden');
          return;
        }
        var categoryChoices = JSON.parse($('#categoryChoices').val());
        var html = "<div class='articleCategory clearfix'>\n ";
        html += '<select class="form-control width-input-large pull-left" name="categoryIds[][categoryId]" style="margin-bottom: 12px">\n <option value="0">请选择所属栏目</option>';
        for (var categoryId in categoryChoices) {
          html += '<option value="' + categoryId + '">' + categoryChoices[categoryId] + '</option>';
        }
        html += '</select>\n <span class="delete-item-btn"><i class="cd-icon cd-icon-close"></i></span>\n </div>';
        $('#categories').append(html);
      });

      $('body').on('click', '.delete-item-btn', function (event) {
        $('.categoriesTip').addClass('hidden');
        if ($('.articleCategory').length > 1){
          $(event.currentTarget).parent('.articleCategory').remove();
        }
      })
    };
});