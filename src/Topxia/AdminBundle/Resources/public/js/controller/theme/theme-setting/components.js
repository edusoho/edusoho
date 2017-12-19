define(function(require, exports, module) {
    require('jquery.sortable');
    exports.run = function() {
      var $themeEditContent = $('.js-theme-component');
      var componentSetting = $.parseJSON($('#componet-setting').text());
      var $modal = $('#modal')
      var $list = $(".module-item-list").sortable({
        distance: 20,
        itemSelector: '.theme-edit-item',
        onDrop: function (item, container, _super) {
          _super(item, container);
          $themeEditContent.trigger('save_sort', getConfig());
        },
      });

      var getConfig = function(){
        var $actives = $themeEditContent.find('input[type=checkbox]:checked');
        var setting = {};
        $actives.each(function(){
           var code = $(this).data('code');
           setting[code] = componentSetting[code];
        });
        
        return {
          blocks: {
            left: setting
          }
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
        var code = $(this).closest('li').find('.check-block').data('code');
        var url = $this.data('url');

        $.get(url, {config: componentSetting[code]}, function(html){
          $modal.html(html)
          $modal.modal('show');
        });
      })

      $themeEditContent.on("save_part_config", function(event, data){
        componentSetting[data.code] = $.extend(componentSetting[data.code], data);
        $("#"+ componentSetting[data.code]['id']).find('.col-md-4').eq(1).text(componentSetting[data.code].title);
        $themeEditContent.trigger('save_sort', getConfig());
      });
    }
});