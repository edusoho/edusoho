define(function(require, exports, module) {
    require('jquery.sortable');
    exports.run = function() {
      var $themeEditContent = $('.js-theme-component');
      var componentSetting = $.parseJSON($('#componet-setting').text()), $modal = $('#modal'), saveKey = $themeEditContent.data('configKey');
console.log(componentSetting);
      var $list = $(".module-item-list").sortable({
        distance: 20,
        itemSelector: '.theme-edit-item',
        onDrop: function (item, container, _super) {
          _super(item, container);
          $themeEditContent.trigger('save_config', getConfig());
        },
      });

      var getConfig = function(){
        var $actives = $themeEditContent.find('input[type=checkbox]:checked'), setting = {};
        setting[saveKey] = {};
        
        $actives.each(function(){
           var key = $(this).data('componentId');
           setting[saveKey][key] = componentSetting[key];
        });
        
        return {
          blocks: setting
        };
      };
      
      $themeEditContent.on("click", '.check-block', function(event){
        var $this = $(this);
        if ($this.prop('checked') == true) {
            $this.parents('li').find('.item-edit-btn,.item-set-btn').show();
        } else {
            $this.closest('li').find('.item-edit-btn,.item-set-btn').hide();
        }
        $themeEditContent.trigger('save_config', getConfig());
      });

      $themeEditContent.on('click', '.item-edit-btn', function(event){
        var $this = $(this);
        var key = $(this).closest('li').find('.check-block').data('componentId');
        var url = $this.data('url');

        $.get(url, {config: componentSetting[key]}, function(html){
          $modal.html(html)
          $modal.modal('show');
        });
      })

      $themeEditContent.on("save_part_config", function(event, data){
        componentSetting[data.id] = $.extend(componentSetting[data.id], data);
        $("#"+ data.id).find('.col-md-4').eq(1).text(componentSetting[data.id].title);
        $themeEditContent.trigger('save_config', getConfig());
      });

    }
});